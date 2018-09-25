<?php
/** 
 * @package server-infra
 * @subpackage request
 */
class kIpAddressUtils 
{
	const IP_TREE_NODE_VALUE	= 2;
		
	const IP_ADDRESS_TYPE_SINGLE       = 1; // example: 192.168.1.1
	const IP_ADDRESS_TYPE_MASK_ADDRESS = 2; // example: 192.168.1.1/255.255.0.0
	const IP_ADDRESS_TYPE_MASK_CIDR    = 3; // example: 192.168.1.1/24
	const IP_ADDRESS_TYPE_RANGE        = 4; // example: 192.168.1.0-192.168.1.240
	
	const IP_ADDRESS_RANGE_CHAR    = '-';
	const IP_ADDRESS_MASK_CHAR     = '/';
	const IP_ADDRESS_PARTS_DELIMETER = '.';
	
	static protected $isInternalIp = array();
	
	public static function getAddressType($ip)
	{
		$mask     = strpos($ip, self::IP_ADDRESS_MASK_CHAR);
		$range    = strpos($ip, self::IP_ADDRESS_RANGE_CHAR);
		
        if ($mask && !$range) 
        { 
        	$subNet = trim(substr($ip,$mask+1));
        	$isNetAddr = strpos($subNet, self::IP_ADDRESS_PARTS_DELIMETER);
        	$type = $isNetAddr ? self::IP_ADDRESS_TYPE_MASK_ADDRESS : self::IP_ADDRESS_TYPE_MASK_CIDR;
        	return $type;
        } 

        if ($range && !$mask) 
        { 
            return self::IP_ADDRESS_TYPE_RANGE; 
        } 

        if (ip2long($ip) && !$range && !$mask) 
        { 
            return self::IP_ADDRESS_TYPE_SINGLE; 
        } 
        
        return null;
	}
	
	
	public static function isIpInRange($ip, $range)
	{
		$ip = trim($ip);
		$range = trim($range);
		
		$rangeType = self::getAddressType($range);
		if (!$rangeType) {
			if (class_exists('KalturaLog'))
				KalturaLog::err("Cannot identify ip address type for [$range]");
			return false;
		}
		
		switch ($rangeType)
		{
			case self::IP_ADDRESS_TYPE_SINGLE:
				return ip2long($ip) === ip2long($range);

			case self::IP_ADDRESS_TYPE_RANGE:
				$d = strpos($range, self::IP_ADDRESS_RANGE_CHAR);
				$fromIp = trim(ip2long(substr($range,0,$d)));
       			$toIp = trim(ip2long(substr($range,$d+1)));
       			$ip = ip2long($ip);
				return ($ip>=$fromIp && $ip<=$toIp);

			case self::IP_ADDRESS_TYPE_MASK_ADDRESS:
				list ($rangeIp, $rangeMask) = array_map('trim', explode(self::IP_ADDRESS_MASK_CHAR, $range));
				// convert mask address to CIDR
				$long = ip2long($rangeMask);
  				$base = ip2long('255.255.255.255');
  				$rangeMask = 32-log(($long ^ $base)+1,2);
  				if ($rangeMask <= 0){
    				return false;
    			} 
       			$ipBinaryStr = sprintf("%032b",ip2long($ip)); 
       			$netBinaryStr = sprintf("%032b",ip2long($rangeIp)); 
        		return (substr_compare($ipBinaryStr,$netBinaryStr,0,$rangeMask) === 0);
				
			case self::IP_ADDRESS_TYPE_MASK_CIDR:
				list ($rangeIp, $rangeMask) = array_map('trim', explode(self::IP_ADDRESS_MASK_CHAR, $range));
    			if ($rangeMask <= 0){
    				return false;
    			} 
       			$ipBinaryStr = sprintf("%032b",ip2long($ip)); 
       			$netBinaryStr = sprintf("%032b",ip2long($rangeIp)); 
        		return (substr_compare($ipBinaryStr,$netBinaryStr,0,$rangeMask) === 0);
		}
		
		if (class_exists('KalturaLog'))
			KalturaLog::err("IP address type [$rangeType] for [$range] is missing implementation");
		return false;		
	}
	
	public static function isIpInRanges($ip, $ranges)
	{	
		foreach (explode(',', $ranges) as $range)
		{
			if(self::isIpInRange($ip, $range))
			{
				return true;
			}
		}
		return false;
	}
	
	
	public static function isInternalIp($ipAddress = null)
	{
		if (!$ipAddress)
		{
			$ipAddress = infraRequestUtils::getRemoteAddress();
		}
		
		if (isset(self::$isInternalIp[$ipAddress]))
			return self::$isInternalIp[$ipAddress];
		
		if (kConf::hasParam('internal_ip_range'))
		{
			$range = kConf::get('internal_ip_range');

			if(self::isIpInRanges($ipAddress, $range))
			{
				self::$isInternalIp[$ipAddress] = true;
				return true;
			}
		}
		self::$isInternalIp[$ipAddress] = false;
		return false;				
	}

	public static function ipRangeToCIDR($from, $to)
	{
		$result = array();

		while($to >= $from) {
			$maxSize = 32;
			while ($maxSize > 0) {
				$mask = (0xffffffff >> ($maxSize - 1)) ^ 0xffffffff;
				$maskBase = $from & $mask;

				if($maskBase != $from) {
					break;
				}

				$maxSize--;
			}

			$highBit = log($to - $from + 1, 2);
			$maxDiff = 32 - floor($highBit);

			if($maxSize < $maxDiff) {
					$maxSize = $maxDiff;
			}

			$ip = long2ip($from);
			$result[$ip] = $maxSize;
			$from += 1 << (32 - $maxSize);
		}

		return $result;
	}
	
	public static function compressIpRanges($ipRanges)
	{
		$from = 0;
		$to = -2;
		$compressedIps = array();
		foreach($ipRanges as $ipFrom => $ipTo)
		{
			// either create a new ip range
			if ($ipFrom > $to + 1) {
				$from = $ipFrom;
				$to = $ipTo;
				$compressedIps[$ipFrom] = $ipTo;
			}
			else if ($ipTo > $to) { // or extend the current range
				$to = $ipTo;
				$compressedIps[$from] = $ipTo;
			}
		}
	
		return $compressedIps;
	}
	
	/*
	 * insert ip ranges array into an ip binary tree built from the ip bits.
	 * The end node will be set with the given value
	 */
	public static function insertRangesIntoIpTree($ipTree, $rangesArr, $value)
	{
		// run over ips and create an array of ip ranges
		$ips = array();

		foreach($rangesArr as $rangesItem)
		{
			$ranges = explode(',', $rangesItem);

			for($i = 0; $i < count($ranges); $i++)
			{
				$range = $ranges[$i];

				$rangeType = self::getAddressType($range);

				switch ($rangeType)
				{
					case self::IP_ADDRESS_TYPE_SINGLE:
						$fromIp = $toIp = ip2long($range);
						break;

					case self::IP_ADDRESS_TYPE_RANGE:
						$d = strpos($range, self::IP_ADDRESS_RANGE_CHAR);
						$fromIp = ip2long(substr($range, 0, $d));
						$toIp = ip2long(substr($range, $d + 1));
						continue;

					case self::IP_ADDRESS_TYPE_MASK_ADDRESS:
						list ($rangeIp, $rangeMask) = array_map('trim', explode(self::IP_ADDRESS_MASK_CHAR, $range));
						// convert mask address to CIDR
						$long = ip2long($rangeMask);
						$fromIp = ip2long('255.255.255.255');
						$rangeMask = 32 - log(($long ^ $base) + 1, 2);
						$toIp = $fromIp + (1 << $rangeMask);
						break;

					case self::IP_ADDRESS_TYPE_MASK_CIDR:
						list ($rangeIp, $rangeMask) = array_map('trim', explode(self::IP_ADDRESS_MASK_CHAR, $range));
						$fromIp = ip2long($rangeIp);
						$toIp = $fromIp + (1 << $rangeMask); 
						break;
				}

				if (!array_key_exists($fromIp, $ips)) {
					$ips[$fromIp] = $toIp;
				}
				else if ($ips[$fromIp] < $toIp) {
					$ips[$fromIp] = $toIp;
				}
			}
		}

		// compress ip ranges 
		$ips = self::compressIpRanges($ips);

		// turn each ip range into a CIDR and add to the ipTree
		foreach($ips as $fromIp => $toIp)
		{
			$rangeCIDRs = self::ipRangeToCIDR($fromIp, $toIp);

			foreach($rangeCIDRs as $rangeIp => $rangeMask)
			{
				$netBinaryStr = sprintf('%032b', ip2long($rangeIp));
				$binIp = str_split(substr($netBinaryStr, 0, $rangeMask));
				$root = &$ipTree;

				foreach($binIp as $bit)
				{
					if (!isset($root[$bit])) {
						$root[$bit] = array();
					}

					$root = &$root[$bit];
				}

				if (!isset($root[self::IP_TREE_NODE_VALUE])) {
						$root[self::IP_TREE_NODE_VALUE] = $value;
				} else {
						$root[self::IP_TREE_NODE_VALUE] .= ",$value";
				}
			}
		}
	}

	/**
	 * Filter an IP using the optimized ipTree
	 * The function returns an array of the values found in the matching tree nodes
	 * 
	 * @param string $ip
	 * @param array $ipTree
	 * 
	 * @return array
	 */
	public static function filterTreeByIp($ip, $ipTree)
	{
		$netBinaryStr = sprintf('%032b', ip2long($ip));
		$binIp = str_split($netBinaryStr);

		$values = array();

		$root = $ipTree;
		foreach($binIp as $bit)
		{
			if (isset($root[self::IP_TREE_NODE_VALUE])) {
				$values[] = $root[self::IP_TREE_NODE_VALUE];
			}

			if (!isset($root[$bit]))
				break;

			$root = $root[$bit];
		}

		return $values;
	}
	
}