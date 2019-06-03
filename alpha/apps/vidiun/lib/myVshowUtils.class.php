<?php

require_once ( __DIR__ . "/myEntryUtils.class.php");

class myVshowUtils
{
	public static function getWidgetCmdUrl($vdata, $cmd = "") //add, vshow, edit
	{
		$domain = requestUtils::getRequestHost();

		$baseCmd = "$domain/index.php/veditorservices/redirectWidgetCmd?vdata=$vdata&cmd=$cmd";

		return $baseCmd;
	}


	public static function createGenericWidgetHtml ( $partner_id, $subp_id, $partner_name ,  $widget_host  , $vshow_id , $user_id , $size='l' , $align='l', $version=null , $version_vshow_name=null , $version_vshow_description=null)
	{
/*		global $partner_id, $subp_id, $partner_name;
		global $WIDGET_HOST;
	*/
	    $media_type = 2;
	    $widget_type = 3;
	    $entry_id = null;

	     // add the version as an additional parameter
		$domain = $widget_host; 
		$swf_url = "/index.php/widget/$vshow_id/" .
			( $entry_id ? $entry_id : "-1" ) . "/" .
			( $media_type ? $media_type : "-1" ) . "/" .
			( $widget_type ? $widget_type : "3" ) . "/" . // widget_type=3 -> WIKIA
			( $version ? "$version" : "-1" );

		$current_widget_vshow_id_list[] = $vshow_id;

		$vshowCallUrl = "$domain/index.php/browse?vshow_id=$vshow_id";
		$widgetCallUrl = "$vshowCallUrl&browseCmd=";
		$editCallUrl = "$domain/index.php/edit?vshow_id=$vshow_id";

	/*
	  widget3:
	  url:  /widget/:vshow_id/:entry_id/:vmedia_type/:widget_type/:version
	  param: { module: browse , action: widget }
	 */
	    if ( $size == "m")
	    {
	    	// medium size
	    	$height = 198 + 105;
	    	$width = 267;
	    }
	    else
	    {
	    	// large size
	    	$height = 300 + 105 + 20;
	    	$width = 400;
	    }

		$root_url = "" ; //getRootUrl();

	    $str = "";//$extra_links ; //"";

	    $external_url = "http://" . @$_SERVER["HTTP_HOST"] ."$root_url";

		$share = "TODO" ; //$titleObj->getFullUrl ();

		// this is a shorthand version of the vdata
	    $links_arr = array (
	    		"base" => "$external_url/" ,
	    		"add" =>  "Special:VidiunContributionWizard?vshow_id=$vshow_id" ,
	    		"edit" => "Special:VidiunVideoEditor?vshow_id=$vshow_id" ,
	    		"share" => $share ,
	    	);

	    $links_str = str_replace ( array ( "|" , "/") , array ( "|01" , "|02" ) , base64_encode ( serialize ( $links_arr ) ) ) ;

		$vidiun_link = "<a href='http://www.vidiun.com' style='color:#bcff63; text-decoration:none; '>Vidiun</a>";
		$vidiun_link_str = "A $partner_name collaborative video powered by  "  . $vidiun_link;

		$flash_vars = array (  "CW" => "gotoCW" ,
	    						"Edit" => "gotoEdit" ,
	    						"Editor" => "gotoEditor" ,
								"Vidiun" => "",//gotoVidiunArticle" ,
								"Generate" => "" , //gotoGenerate" ,
								"share" => "" , //$share ,
								"WidgetSize" => $size );

		// add only if not null
		if ( $version_vshow_name ) $flash_vars["Title"] = $version_vshow_name;
		if ( $version_vshow_description ) $flash_vars["Description"] = $version_vshow_description;

		$swf_url .= "/" . $links_str;
	   	$flash_vars_str = http_build_query( $flash_vars , "" , "&" )		;

	    $widget = /*$extra_links .*/
			 '<object id="vidiun_player_' . (int)microtime(true) . '" type="application/x-shockwave-flash" allowScriptAccess="always" allowNetworking="all" height="' . $height . '" width="' . $width . '" data="'.$domain. $swf_url . '">'.
				'<param name="allowScriptAccess" value="always" />'.
				'<param name="allowNetworking" value="all" />'.
				'<param name="bgcolor" value=#000000 />'.
				'<param name="movie" value="'.$domain. $swf_url . '"/>'.
				'<param name="flashVars" value="' . $flash_vars_str . '"/>'.
				'<param name="wmode" value="opaque"/>'.
				$vidiun_link .
				'</object>' ;

			"</td></tr><tr><td style='background-color:black; color:white; font-size: 11px; padding:5px 10px; '>$vidiun_link</td></tr></table>";

		if ( $align == 'r' )
		{
			$str .= '<div class="floatright"><span>' . $widget . '</span></div>';
		}
		elseif ( $align == 'l' )
		{
			$str .= '<div class="floatleft"><span>' . $widget . '</span></div>';
		}
		elseif ( $align == 'c' )
		{
			$str .= '<div class="center"><div class="floatnone"><span>' . $widget . '</span></div></div>';
		}
		else
		{
			$str .= $widget;
		}

		return $str ;
	}
	/**
	 * Will create the URL for the embedded player for this vshow_id assuming is placed on the current server with the same http protocol.
	 * @param string $vshow_id
	 * @return string URL
	 */
	public static function getEmbedPlayerUrl ( $vshow_id , $entry_id , $is_roughcut = false, $vdata = "")
	{
		// TODO - PERFORMANCE - cache the versions per vshow_id
		// - if an entry_id exists - don't fetch the version for the vshow

		$vshow = vshowPeer::retrieveByPK( $vshow_id );
		if ( !$vshow )
		return array("", "");

		$media_type = entry::ENTRY_MEDIA_TYPE_SHOW;

		if ($entry_id)
		{
			$entry = entryPeer::retrieveByPK($entry_id);
			if ($entry)
			$media_type = $entry->getMediaType();

			// if the entry is one of the vshow roughcuts we want to share the latest roughcut
			if ($entry->getType() == entryType::MIX)
			$entry_id = -1;
		}

		if ( $is_roughcut )
		{
			$show_entry_id = $vshow->getShowEntryId();
			$show_entry = entryPeer::retrieveByPK( $show_entry_id );
			if ( !$show_entry ) return null;
			$media_type = $show_entry->getMediaType();

			$show_version = $show_entry->getLastVersion();
			// set the entry_id to -1 == we want to show the roughcut, not a specific entry.
			$entry_id = $show_entry_id;
		}
		else
		{
			$show_version = -1;
		}

		$partnerId = $vshow->getPartnerId();

		$swf_url = "/index.php/widget/$vshow_id/" . ( $entry_id ? $entry_id : "-1" ) . "/" . ( $media_type ? $media_type : "-1" ) ;

		$domain = requestUtils::getRequestHost();

		$vshowName = $vshow->getName();

		if ($entry_id >= 0)
			$headerImage = $domain.'/index.php/browse/getWidgetImage/entry_id/'.$entry_id;
		else
			$headerImage = $domain.'/index.php/browse/getWidgetImage/vshow_id/'.$vshow_id;


		if (in_array($partnerId, array(1 , 8, 18, 200))) // we're sharing a wiki widget
		{
			$footerImage = $domain.'/index.php/browse/getWidgetImage/partner_id/'.$partnerId;

			$baseCmd = self::getWidgetCmdUrl($vdata);

			$widgetCallUrl = $baseCmd."add";
			$vshowCallUrl = $baseCmd."vshow";
			$editCallUrl = $baseCmd."edit";

			$genericWidget =
			'<object type="application/x-shockwave-flash" allowScriptAccess="always" allowNetworking="all" height="405" width="400" data="'.$domain. $swf_url . '/4/-1/'.$vdata.'"/>'.
			'<param name="allowScriptAccess" value="always" />'.
			'<param name="allowNetworking" value="all" />'.
			'<param name="bgcolor" value="#000000" />'.
			'<param name="movie" value="'.$domain. $swf_url . '/4/-1/'.$vdata.'"/>'.
			'</object>';

			$myspaceWidget = <<<EOT
<table cellpadding="0" cellspacing="0" style="width:400px; margin:0 auto;">
	<tr style="background-color:black;">
		<th colspan="2" style="background-color:black; background: url($headerImage) 0 0 no-repeat;">
			<a href="$vshowCallUrl" style="display:block; height:30px; overflow:hidden;"></a>
		</th>
	</tr>
	<tr style="background-color:black;">
		<td colspan="2">
			<object type="application/x-shockwave-flash" allowScriptAccess="never" allowNetworking="internal" height="320" width="400" data="{$domain}{$swf_url}/1/-1/{$vdata}">
				<param name="allowScriptAccess" value="never" />
				<param name="allowNetworking" value="internal" />
				<param name="bgcolor" value="#000000" />
				<param name="movie" value="{$domain}{$swf_url}/1/-1/{$vdata}" />
			</object>
		</td>
	</tr>
	<tr style="background-color:black;">
		<td style="height:33px;"><a href="$widgetCallUrl" style="display:block; width:199px; height:33px; background:black url(http://www.vidiun.com/images/widget/wgt_btns2.gif) center 0 no-repeat; border-right:1px solid #000; overflow:hidden;"></a></td>
		<td style="height:33px;"><a href="$editCallUrl" style="display:block; width:199px; height:33px; background:black url(http://www.vidiun.com/images/widget/wgt_btns2.gif) center -33px no-repeat; border-left:1px solid #555; overflow:hidden;"></a></td>
	</tr>
	<tr>
		<td colspan="2" style="background-color:black; border-top:1px solid #222; background: url($footerImage) 0 0 no-repeat;">
			<a href="$domain" style="display:block; height:20px; overflow:hidden;"></a>
		</td>
	</tr>
</table>
EOT;
return array($genericWidget, $myspaceWidget);
		}

		$vshowCallUrl = "$domain/index.php/browse?vshow_id=$vshow_id";
		if ($entry_id >= 0)
		$vshowCallUrl .= "&entry_id=$entry_id";

		$widgetCallUrl = "$vshowCallUrl&browseCmd=";

		$editCallUrl = "$domain/index.php/edit?vshow_id=$vshow_id";
		if ($entry_id >= 0)
		$editCallUrl .= "&entry_id=$entry_id";

		if (in_array($partnerId, array(315, 387)))
		{
			$genericWidget =
			'<object type="application/x-shockwave-flash" allowScriptAccess="always" allowNetworking="all" height="407" width="400" data="'.$domain. $swf_url . '/21">'.
			'<param name="allowScriptAccess" value="always" />'.
			'<param name="allowNetworking" value="all" />'.
			'<param name="bgcolor" value="#000000" />'.
			'<param name="flashvars" value="hasHeadline=1&hasBottom=1&sourceLink=remixurl" />';
			'<param name="movie" value="'.$domain. $swf_url . '/21"/>'.
			'</object>';
		}
		else if (in_array($partnerId, array(250)))
		{
			$genericWidget =
			'<object type="application/x-shockwave-flash" allowScriptAccess="always" allowNetworking="all" height="407" width="400" data="'.$domain. $swf_url . '/40">'.
			'<param name="allowScriptAccess" value="always" />'.
			'<param name="allowNetworking" value="all" />'.
			'<param name="bgcolor" value="#000000" />'.
			'<param name="flashvars" value="hasHeadline=1&hasBottom=1&sourceLink=remixurl" />';
			'<param name="movie" value="'.$domain. $swf_url . '/40"/>'.
			'</object>';
		}
		else if (in_array($partnerId, array(321,449)))
		{
			$genericWidget =
			'<object type="application/x-shockwave-flash" allowScriptAccess="always" allowNetworking="all" height="407" width="400" data="'.$domain. $swf_url . '/60">'.
			'<param name="allowScriptAccess" value="always" />'.
			'<param name="allowNetworking" value="all" />'.
			'<param name="bgcolor" value="#000000" />'.
			'<param name="flashvars" value="hasHeadline=1&hasBottom=1&sourceLink=remixurl" />';
			'<param name="movie" value="'.$domain. $swf_url . '/60"/>'.
			'</object>';
		}		
		else
		{
			$genericWidget =
			'<object type="application/x-shockwave-flash" allowScriptAccess="always" allowNetworking="all" height="340" width="400" data="'.$domain. $swf_url . '/2">'.
			'<param name="allowScriptAccess" value="always" />'.
			'<param name="allowNetworking" value="all" />'.
			'<param name="bgcolor" value="#000000" />'.
			'<param name="movie" value="'.$domain. $swf_url . '/2"/>'.
			'</object>';
		}

		$myspaceWidget =
		'<table cellpadding="0" cellspacing="0" style="width:400px; margin:6px auto; padding:0; background-color:black; border:1px solid black;">'.
		'<tr>'.
		'<th colspan="2" style="background-color:black; background: url('.$headerImage.') 0 0 no-repeat;"><a href="'.$vshowCallUrl.'" style="display:block; height:30px; overflow:hidden;"></a></th>'.
		'</tr>'.
		'<tr>'.
		'<td colspan="2">'.
		'<object type="application/x-shockwave-flash" allowScriptAccess="never" allowNetworking="internal" height="320" width="400" data="'.$domain. $swf_url . '/1">'.
		'<param name="allowScriptAccess" value="never" />'.
		'<param name="allowNetworking" value="internal" />'.
		'<param name="bgcolor" value="#000000" />'.
		'<param name="movie" value="'.$domain. $swf_url . '/1"/>'.
		'</object>'.
		'</td>'.
		'</tr>'.
		'<tr>'.
		'<td style="height:33px;"><a href="'.$widgetCallUrl.'contribute" style="display:block; width:199px; height:33px; background: url('.$domain.'/images/widget/wgt_btns2.gif) center 0 no-repeat; border-right:1px solid #000; overflow:hidden;"></a></td>'.
		'<td style="height:33px;"><a href="'.$editCallUrl.'" style="display:block; width:199px; height:33px; background: url('.$domain.'/images/widget/wgt_btns2.gif) center -33px no-repeat; border-left:1px solid #555; overflow:hidden;"></a></td>'.
		'</tr>'.
		'</table>';

		return array($genericWidget, $myspaceWidget);
	}

	/**
	 * Will create the URL for the vshow_id to be used as an HTML link
	 *
	 * @param string $vshow_id
	 * @return string URL link
	 */
	public static function getUrl ( $vshow_id )
	{
		return requestUtils::getWebRootUrl() . "browse?vshow_id=$vshow_id";
	}

	/**
	 * Will return an array of vshows that are 'related' to a given show
	 *
	 * @param string $vshow_id
	 * @return array of
	 */
	public static function getRelatedShows( $vshow_id, $vuser_id, $amount )
	{
		$c = new Criteria();
		$c->addJoin( vshowPeer::PRODUCER_ID, vuserPeer::ID, Criteria::INNER_JOIN);
		$c->add( vshowPeer::ID, 10000, Criteria::GREATER_EQUAL);

		//$c->add( vshowPeer::PRODUCER_ID, $vuser_id );

		// our related algorithm is based on finding shows that have similar 'heavy' tags
		if( $vshow_id )
		{
			$vshow = vshowPeer::retrieveByPK( $vshow_id );
			if( $vshow )
			{
				$tags_string = $vshow->getTags();
				if( $tags_string )
				{
					$tagsweight = array();
					foreach( vtagword::getTagsArray( $tags_string ) as $tag )
					{
						$tagsweight[$tag] = vtagword::getWeight( $tag );
					}
					arsort( $tagsweight );
					$counter = 0;
					foreach( $tagsweight as $tag => $weight )
					{
						if( $counter++ > 2 ) break;
						else
						{
							//we'll be looking for shows that have similar top tags (3 in this case)
							$c->addOr( vshowPeer::TAGS, '%'.$tag.'%', Criteria::LIKE );
						}
					}
				}

				// and of course, we don't want the show itself
				$c->addAnd( vshowPeer::ID, $vshow_id, Criteria::NOT_IN);
			}
		}
		// we want recent ones
		$c->addDescendingOrderByColumn( vshowPeer::UPDATED_AT );
		$c->setLimit( $amount );

		$shows = vshowPeer::doSelectJoinVuser( $c );

		//did we get enough?
		$amount_related = count ($shows);
		if(  $amount_related < $amount )
		{
			// let's get some more, which are not really related, but recent
			$c = new Criteria();
			$c->addJoin( vshowPeer::PRODUCER_ID, vuserPeer::ID, Criteria::INNER_JOIN);
			$c->addDescendingOrderByColumn( vshowPeer::UPDATED_AT );
			$c->setLimit( $amount - $amount_related );
			$moreshows = vshowPeer::doSelectJoinVuser( $c );
			return array_merge( $shows, $moreshows );
		}

		return $shows;

	}

	/**
	 * Will return formatted array of vshows data for shows that are 'related' to a given show
	 *
	 * @param string $vshow_id
	 * @return array of
	 */
	public static function getRelatedShowsData ( $vshow_id, $vuser_id = null, $amount = 50 )
	{
		$vshow_list = self::getRelatedShows ( $vshow_id, $vuser_id, $amount );

		$vshowdataarray = array();

		foreach( $vshow_list as $vshow )
		{

			$data = array ( 'id' => $vshow->getId(),
			'thumbnail_path' => $vshow->getThumbnailPath(),
			'show_entry_id' => $vshow->getShowEntryId(),
			'name' => $vshow->getName(),
			'producer_name' => $vshow->getvuser()->getScreenName(),
			'views' => $vshow->getViews()
			);
			$vshowdataarray[] = $data;
		}
		return $vshowdataarray;
	}

	public static function createTeamImage ( $vshow_id )
	{
		self::createTeam1Image($vshow_id);
		self::createTeam2Image($vshow_id);
	}

	/**
	 * Creates an combined image of the producer and some of the contributors
	 *
	 * @param int $vshow_id
	 */
	const DIM_X = 26;
	const DIM_Y = 23;
	public static function createTeam1Image ( $vshow_id )
	{
		try
		{
			$contentPath = myContentStorage::getFSContentRootPath() ;

			$vshow = vshowPeer::retrieveByPK( $vshow_id );
			if ( ! $vshow ) return NULL;

			// the canvas for the output -
			$im = imagecreatetruecolor(120 , 90 );

			$logo_path = vFile::fixPath( SF_ROOT_DIR.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'main'.DIRECTORY_SEPARATOR.'vLogoBig.gif' );
			$logoIm = imagecreatefromgif( $logo_path );
			$logoIm_x = imagesx($logoIm);
			$logoIm_y = imagesy($logoIm);
			imagecopyresampled($im, $logoIm, 0, 0, 0 , 0 , $logoIm_x *0.25 ,$logoIm_y*0.25, $logoIm_x , $logoIm_y);
			imagedestroy($logoIm);

			// get producer's image
			$producer = vuser::getVuserById( $vshow->getProducerId() );
			$producer_image_path = vFile::fixPath(  $contentPath . $producer->getPicturePath () );
			if (file_exists($producer_image_path))
			{
				list($sourcewidth, $sourceheight, $type, $attr, $srcIm ) = myFileConverter::createImageByFile( $producer_image_path );

				$srcIm_x = imagesx($srcIm);
				$srcIm_y = imagesy($srcIm);
				// producer -
				imagecopyresampled($im, $srcIm, 0, 0, $srcIm_x * 0.1 , $srcIm_y * 0.1 , self::DIM_X * 2  ,self::DIM_Y * 2, $srcIm_x * 0.9 , $srcIm_y * 0.9 );
				imagedestroy($srcIm);
			}

			// fetch as many different vusers as possible who contributed to the vshow
			// first entries willcome up first
			$c = new Criteria();
			$c->add ( entryPeer::VSHOW_ID , $vshow_id );
			$c->add ( entryPeer::TYPE , entryType::MEDIA_CLIP, Criteria::EQUAL );
			//$c->add ( entryPeer::PICTURE, null, Criteria::NOT_EQUAL );
			$c->setLimit( 16 ); // we'll need 16 images of contributers
			$c->addGroupByColumn(entryPeer::VUSER_ID);
			$c->addDescendingOrderByColumn ( entryPeer::CREATED_AT );
			$entries = entryPeer::doSelectJoinvuser( $c );

			if ( $entries == NULL || count ( $entries ) == 0 )
			{
				imagedestroy($im);
				return;
			}

			//		$entry_index = 0;
			$entry_list_len = count ( $entries );
			reset ( $entries );

			if ( $entry_list_len > 0 )
			{
				/*
				 $pos = array(2,3,4, 7,8,9, 10,11,12,13,14, 15,16,17,18,19);
				 $i = 20;
				 while(--$i)
				 {
					$p1 = rand(0, 15);
					$p2 = rand(0, 15);
					$p = $pos[$p1];
					$pos[$p1] = $pos[$p2];
					$pos[$p2] = $p;
					}

					$i = count($entries);
					while($i--)
					{
					$x = current($pos) % 5;
					$y = floor(current($pos) / 5);
					next($pos);
					self::addVuserPictureFromEntry ( $contentPath , $im ,$entries , $x , $y );
					}
					*/

				for ( $y = 0 ; $y <= 1 ; ++$y )
				for ( $x = 2 ; $x <= 4 ; ++ $x  )
				{
					self::addVuserPictureFromEntry ( $contentPath , $im ,$entries , $x , $y );
				}

				for ( $y = 2 ; $y <= 3 ; ++$y )
				for ( $x = 0 ; $x <= 4 ; ++ $x  )
				{
					self::addVuserPictureFromEntry ( $contentPath , $im ,$entries , $x , $y );
				}
			}
			else
			{
				// no contributers - need to create some other image
			}


			// add the clapper image on top


			$clapper_path = vFile::fixPath( SF_ROOT_DIR.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'myvidiun'.DIRECTORY_SEPARATOR.'productionicon.png' );
			$clapperIm = imagecreatefrompng( $clapper_path );
			imagecopyresampled($im, $clapperIm, ( 1.2 * self::DIM_X ) , (1.2 * self::DIM_Y), 0, 0, self::DIM_X ,self::DIM_Y , imagesx($clapperIm) , imagesy($clapperIm) );
			imagedestroy($clapperIm);

			$path = vFile::fixPath( $contentPath.$vshow->getTeamPicturePath() );

			vFile::fullMkdir($path);

			imagepng($im, $path);
			imagedestroy($im);

			$vshow->setHasTeamImage ( true );
			$vshow->save();
		}
		catch ( Exception $ex )
		{
			// nothing much we can do here !

		}
	}

	public static function createTeam2Image ( $vshow_id )
	{
		try
		{
			$vshow = vshowPeer::retrieveByPK( $vshow_id );
			if ( ! $vshow ) return NULL;

			$contentPath = myContentStorage::getFSContentRootPath() ;

			// TODO - maybe start from some vidiun background - so if image is not full - still interesting
			$im = imagecreatetruecolor(24 * 7 - 1, 24  * 2 - 1);

			$logo_path = vFile::fixPath( SF_ROOT_DIR.'/web/images/browse/contributorsBG.gif');
			$im = imagecreatefromgif( $logo_path );

			// fetch as many different vusers as possible who contributed to the vshow
			// first entries will come up first
			$c = new Criteria();
			$c->add ( entryPeer::VSHOW_ID , $vshow_id );
			$c->add ( entryPeer::TYPE , entryType::MEDIA_CLIP, Criteria::EQUAL );
			//$c->add ( entryPeer::PICTURE, null, Criteria::NOT_EQUAL );
			$c->setLimit( 14 ); // we'll need 14 images of contributers
			$c->addGroupByColumn(entryPeer::VUSER_ID);
			$c->addDescendingOrderByColumn ( entryPeer::CREATED_AT );
			$entries = BaseentryPeer::doSelectJoinvuser( $c );

			if ( $entries == NULL || count ( $entries ) == 0 )
			{
				imagedestroy($im);
				return;
			}

			$entry_list_len = count ( $entries );
			reset ( $entries );

			if ( $entry_list_len > 0 )
			{
				for ( $y = 0 ; $y <= 1 ; ++$y )
				for ( $x = 0 ; $x <= 6 ; ++ $x  )
				{
					self::addVuserPictureFromEntry ( $contentPath , $im ,$entries , $x , $y, 1, 24, 24 );
				}
			}
			else
			{
				// no contributers - need to create some other image
			}


			$path = vFile::fixPath( $contentPath.$vshow->getTeam2PicturePath() );

			vFile::fullMkdir($path);

			imagepng($im, $path);
			imagedestroy($im);

			$vshow->setHasTeamImage ( true );
			$vshow->save();
		}
		catch ( Exception $ex )
		{
			// nothing much we can do here !

		}
	}

	private static function addVuserPictureFromEntry ( $contentPath , $im , &$entries , $x , $y , $border = 1, $width = self::DIM_X, $height = self::DIM_Y)
	{
		$entry = current ($entries );

		if ( $entry == NULL )
		{
			// for now - if there are not enough images - stop !
			return ;

			// if we reach here - we want to rotate the images we already used
			reset ( $entries );
			$entry = current ($entries );
		}
		$vuser =  $entry->getVuser();
		$vuser_image_path = vFile::fixPath(  $contentPath . $vuser->getPicturePath () );

		if (file_exists($vuser_image_path))
		{
			list($sourcewidth, $sourceheight, $type, $attr, $vuserIm ) = myFileConverter::createImageByFile( $vuser_image_path );

			if ($vuserIm)
			{
				$vuserIm_x = imagesx($vuserIm);
				$vuserIm_y = imagesy($vuserIm);
				// focus on the ceter of the image - ignore 10% from each side to make the center bigger
				imagecopyresampled($im, $vuserIm, $width * $x , $height * $y, $vuserIm_x * 0.1 , $vuserIm_y * 0.1 , $width - $border  ,$height - $border, $vuserIm_x * 0.9  , $vuserIm_y * 0.9 );
				imagedestroy($vuserIm);
			}
		}
		next ( $entries );
	}

	public static function isSubscribed($vshow_id, $vuser_id, $subscription_type = null)
	{
		$c = new Criteria ();
		$c->add ( VshowVuserPeer::VSHOW_ID , $vshow_id);
		$c->add ( VshowVuserPeer::VUSER_ID , $vuser_id);

		if ($subscription_type !== null)
		$c->add ( VshowVuserPeer::SUBSCRIPTION_TYPE, $subscription_type );

		return VshowVuserPeer::doSelectOne( $c );
	}

	public static function subscribe($vshow_id, $vuser_id, &$message)
	{
		// first check if user already subscribed to this show
		$vshowVuser = self::isSubscribed($vshow_id, $vuser_id);
		if ( $vshowVuser != NULL )
		{
			$message = "You are already subscribed to this Vidiun";
			return false;
		}

		$vshow = vshowPeer::retrieveByPK($vshow_id);
		if (!$vshow)
		{
			$message = "Vidiun $vshow_id doesn't exist";
			return false;
		}

		$vuser = vuserPeer::retrieveByPK($vuser_id);
		if (!$vuser)
		{
			$message = "User $vuser_id doesn't exist";
			return false;
		}

		$showname = $vshow->getName();
		$subscriberscreenname = $vuser->getScreenName();

		// subscribe
		$vshowVuser = new VshowVuser();
		$vshowVuser->setVshowId($vshow_id);
		$vshowVuser->setVuserId($vuser_id);
		$vshowVuser->setSubscriptionType(VshowVuser::VSHOW_SUBSCRIPTION_NORMAL);
		// alert:: VIDIUNS_PRODUCED_ALERT_TYPE_SUBSCRIBER_ADDED
		$vshowVuser->setAlertType(21);
		$vshowVuser->save();

		$message = "You are now subscribed to $showname. You can receive updates and join the discussion.";
		return true;
	}

	public static function unsubscribe( $vshow_id, $vuser_id, &$message )
	{
		// first check if user already subscribed to this show
		$vshowVuser = self::isSubscribed($vshow_id, $vuser_id, VshowVuser::VSHOW_SUBSCRIPTION_NORMAL);

		if ( !$vshowVuser )
		{
			$vshow = vshowPeer::retrieveByPK($vshow_id);
			if (!$vshow)
			{
				$message = "Vidiun $vshow_id doesn't exist.";
			}
			else
			{
				$vuser = vuserPeer::retrieveByPK($vuser_id);
				if (!$vuser)
				{
					$message = "User $vuser_id doesn't exist.";
				}
				else
				$message = "Error - You are not subscribed to this Vidiun.";
			}

			return false;
		}

		// ok, we found he entry, so delete it.
		$vshowVuser->delete();
		$message = "You have unsubscribed from this Vidiun.";
		return true;
	}

	public static function canEditVshow ( $vshow_id , $existing_vshow , $livuser_id )
	{
		if ( $existing_vshow == NULL )
		{
			// TODO - some good error -
			// TODO - let's make a list of all errors we encounter and see how we use the I18N and built-in configuration mechanism to maintain the list
			// and later on translate the errors.
			// ERROR::fatal ( 12345 , "Vshow with id [" .  $vshow_id . "] does not exist in the system. This is either an innocent mistake or you are a wicked bastard" );
			// TODO - think of our policy - what do we do if we notice what looks like an attemp to harm the system ?
			// because the system is not stable, mistakes like this one might very possibly be innocent, but later on - what should happen in XSS / SQL injection /
			// attemp to insert malformed data ?

			return false;
		}

		// make sure the logged-in user is allowed to access this vshow in 2 aspects:
		// 1. - it is produced by him or a template
		if ( $existing_vshow->getProducerId() != $livuser_id )
		{
			//ERROR::fatal ( 10101 , "User (with id [" . $livuser_id . "] is attempting to modify a vshow with id [$vshow_id] that does not belong to him (producer_id [" . $existing_vshow->getProducerId() . "] !!" );

			return false;
		}

		return true;
	}

	public static function fromatPermissionText ( $vshow_id , $vshow = null )
	{
		if ( $vshow == NULL )
		{
			$vshow = vshowPeer::retrieveByPK ( $vshow_id );
		}

		if ( !$vshow )
		{
			// ERROR !
			return "";
		}

		$pwd_permissions = $vshow->getViewPermissions() == vshow::VSHOW_PERMISSION_INVITE_ONLY ||
		$vshow->getEditPermissions() == vshow::VSHOW_PERMISSION_INVITE_ONLY ||
		$vshow->getContribPermissions() == vshow::VSHOW_PERMISSION_INVITE_ONLY;

		// no password protection
		if ( ! $pwd_permissions ) return "";


		$str =
		( $vshow->getViewPermissions() == vshow::VSHOW_PERMISSION_INVITE_ONLY ? "View password " . $vshow->getViewPassword() . " " : "") .
		( $vshow->getContribPermissions() == vshow::VSHOW_PERMISSION_INVITE_ONLY ? "Contribute password " . $vshow->getContribPassword() . " " : "") .
		( $vshow->getEditPermissions() == vshow::VSHOW_PERMISSION_INVITE_ONLY ? "Edit password " . $vshow->getEditPassword() . " " : "") ;

		return $str;
	}

	public static function getViewerType($vshow, $vuserId)
	{
		$viewerType = VshowVuser::VSHOWVUSER_VIEWER_USER; // viewer
		if ($vuserId)
		{
			if ($vshow->getProducerId() == $vuserId) {
				$viewerType = VshowVuser::VSHOWVUSER_VIEWER_PRODUCER; // producer
			}
			else
			{
				if (myVshowUtils::isSubscribed($vshow->getId(), $vuserId))
				$viewerType = VshowVuser::VSHOWVUSER_VIEWER_SUBSCRIBER; // subscriber;
			}
		}

		return $viewerType;
	}

	private static function resetVshowStats ( $target_vshow , $reset_entry_stats = false )
	{
		// set all statistics to 0
		$target_vshow->setComments ( 0 );
		$target_vshow->setRank ( 0 );
		$target_vshow->setViews ( 0 );
		$target_vshow->setVotes ( 0 );
		$target_vshow->setFavorites ( 0 );
		if ( $reset_entry_stats )
		{
			$target_vshow->setEntries ( 0 );
			$target_vshow->setContributors ( 0 );
		}
		$target_vshow->setSubscribers ( 0 );
		$target_vshow->setNumberOfUpdates ( 0 );

		$target_vshow->setCreatedAt( time() );
		$target_vshow->setUpdatedAt( time() );

	}

	public static function shalowCloneById ( $source_vshow_id , $new_prodcuer_id )
	{
		$vshow = vshowPeer::retrieveByPK( $source_vshow_id );
		if ( $vshow ) return self::shalowClone( $vshow , $new_prodcuer_id );
		else NULL;
	}

	public static function shalowClone ( vshow $source_vshow , $new_prodcuer_id )
	{
		$target_vshow = $source_vshow->copy();

		$target_vshow->setProducerId( $new_prodcuer_id ) ;

		$target_vshow->save();

		self::resetVshowStats( $target_vshow , true );
		if (!$source_vshow->getEpisodeId())
			$target_vshow->setEpisodeId( $source_vshow->getId());
		//$target_vshow->setHasRoughcut($source_vshow->getHasRoughcut());

		$target_show_entry = $target_vshow->createEntry ( entry::ENTRY_MEDIA_TYPE_SHOW , $new_prodcuer_id );

		$content = myContentStorage::getFSContentRootPath();
		$source_thumbnail_path = $source_vshow->getThumbnailPath();
		$target_vshow->setThumbnail ( null );
		$target_vshow->setThumbnail ( $source_vshow->getThumbnail() );
		$target_thumbnail_path = $target_vshow->getThumbnailPath();

//		myContentStorage::moveFile( $content . $source_thumbnail_path , $content . $target_thumbnail_path , false , true );

		$target_vshow->save();

		// copy the show_entry file content
		$source_show_entry = entryPeer::retrieveByPK( $source_vshow->getShowEntryId() );

		$source_show_entry_data_key = $source_show_entry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_DATA);
		$target_show_entry->setData ( null );
		$target_show_entry->setData ( $source_show_entry->getData() );
		$target_show_entry_data_key = $target_show_entry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_DATA);
		
		$target_show_entry->setName ( $source_show_entry->getName() );
		$target_show_entry->setLengthInMsecs( $source_show_entry->getLengthInMsecs() );
		
		vFileSyncUtils::softCopy($source_show_entry_data_key, $target_show_entry_data_key);
		//myContentStorage::moveFile( $content . $source_show_entry_path , $content . $target_show_entry_path , false , true );

		myEntryUtils::createThumbnail($target_show_entry, $source_show_entry, true);
		
//		$target_vshow->setHasRoughcut(true);
//		$target_vshow->save();
		
		$target_show_entry->save();

		return $target_vshow;
	}


	// use the entry's thumbnail as this vshow's thumbnail
	public static function updateThumbnail ( $vshow , entry $entry , $should_force = false )
	{
		// We don't want to copy thumbnails of entries that are not ready - they are bad and will later be replaced anyway
		if ( $entry->getThumbnail() != null && $entry->isReady() )
		{
			$show_entry = $vshow->getShowEntry();
			return myEntryUtils::createThumbnail ( $show_entry , $entry , $should_force );
		}
		return false;
	}


	public static function getVshowAndEntry ( &$vshow_id , &$entry_id )
	{
		$error = null;
		$vshow = null;
		$entry = null;
		$error_obj = null;
		if ( $entry_id == NULL || $entry_id == "-1" )
		{
			if ($vshow_id)
			{
				$vshow = vshowPeer::retrieveByPK( $vshow_id );
				if ( ! $vshow )
				{
					$error =  APIErrors::INVALID_VSHOW_ID; // "vshow [$vshow_id] does not exist";
					$error_obj = array ( $error , $vshow_id  );
				}
				else
				{
					$entry_id = $vshow->getShowEntryId();
					$entry = $vshow->getShowEntry();
				}
			}
		}
		else
		{
			$entry = entryPeer::retrieveByPK($entry_id);
			if ( $entry )
			{
				$vshow = @$entry->getVshow();
				$vshow_id = $entry->getVshowId();
			}
		}

		if ( $entry == NULL )
		{
			$error =  APIErrors::INVALID_ENTRY_ID; //"No such entry [$entry_id]" ;
			$error_obj = array ( $error , "entry" , $entry_id  );
		}

		return array ( $vshow , $entry , $error , $error_obj );
	}

	/*
	 * @param unknown_type $generic_id
	 * A generic_id is a strgin starting with w- or v- or e-
	 * then comes the real id -
	 * 	w- a widget id which is a 32 character md5 string
	 *  v- a vshow id which is an integer
	 *  e- an entry id which is an integer
	 */
// TODO - cache the ids !!!
	public static function getWidgetVshowEntryFromGenericId( $generic_id )
	{
		if ( $generic_id == null )
			return null;
		$prefix = substr ( $generic_id , 0 , 2 );
		if ( $prefix == "w-" )
		{
			$id = substr ( $generic_id , 2 ); // the rest of the string
			$widget = widgetPeer::retrieveByPK( $id , null , widgetPeer::WIDGET_PEER_JOIN_ENTRY +  widgetPeer::WIDGET_PEER_JOIN_VSHOW ) ;
			if ( ! $widget )
				return null;
			$vshow = $widget->getVshow();
			$entry = $widget->getEntry();

			return array ( $widget , $vshow , $entry );
		}
		elseif ( $prefix == "v-" )
		{
			$entryId = -1;
			$id = substr ( $generic_id , 2 ); // the rest of the string
			list ( $vshow , $entry , $error ) = self::getVshowAndEntry ( $id , $entryId );
			if ( $error )	return null;
			return array ( null , $vshow , $entry );
		}
		elseif ( $prefix == "e-" )
		{
			$vShowId = -1;
			$id = substr ( $generic_id , 2 ); // the rest of the string
			list ( $vshow , $entry , $error ) = self::getVshowAndEntry ( $vShowId , $id );
			if ( $error )	return null;
			return array ( null , $vshow , $entry );
		}
		else
		{
			// not a good prefix - why guess ???
			return null;
		}
	}

	/**
	 * Will search for a vshow for the specific partner & key.
	 * The key can be combined from the vuser_id and the group_id
	 * If not found - will create one
	 * If both the vuser_id & group_id are null - always create one
	 */
	public static function getDefaultVshow ( $partner_id , $subp_id, $puser_vuser , $group_id = null , $allow_quick_edit = null , $create_anyway = false , $default_name = null )
	{
		$vuser_id = null;
		// make sure puser_vuser object exists so function will not exit with FATAL
		if($puser_vuser)
		{
			$vuser_id = $puser_vuser->getVuserId();
		}
		$key = $group_id != null ? $group_id : $vuser_id;
		if ( !$create_anyway )
		{
			$c = new Criteria();
			myCriteria::addComment( $c , "myVshowUtils::getDefaultVshow");
			$c->add ( vshowPeer::GROUP_ID , $key );
			$vshow = vshowPeer::doSelectOne( $c );
			if ( $vshow ) return $vshow;
					// no vshow - create using the service
			$name = "{$key}'s generated show'";
		}
		else
		{
			$name = "a generated show'";
		}

		if	( $default_name ) 
			$name = $default_name;
		
		$extra_params = array ( "vshow_groupId" => $key , "vshow_allowQuickEdit" => $allow_quick_edit ); // set the groupId with the key so we'll find it next time round
		$vshow = myPartnerServicesClient::createVshow ( "" , $puser_vuser->getPuserId() , $name , $partner_id , $subp_id , $extra_params );
		
		return $vshow;
	}
	
	public static function getVshowFromPartnerPolicy ( $partner_id, $subp_id , $puser_vuser , $vshow_id , $entry )
	{
	    if ( $vshow_id == vshow::VSHOW_ID_USE_DEFAULT )
        {
            // see if the partner has some default vshow to add to
            $vshow = myPartnerUtils::getDefaultVshow ( $partner_id, $subp_id , $puser_vuser  );
            if ( $vshow ) $vshow_id = $vshow->getId();
        }
		elseif ( $vshow_id == vshow::VSHOW_ID_CREATE_NEW )
        {
            // if the partner allows - create a new vshow 
            $vshow = myPartnerUtils::getDefaultVshow ( $partner_id, $subp_id , $puser_vuser , null , true );
            if ( $vshow ) $vshow_id = $vshow->getId();
        }   
		else
        {
            $vshow = vshowPeer::retrieveByPK( $vshow_id );
        }

        if ( ! $vshow )
        {
            // the partner is attempting to add an entry to some invalid or non-existing vwho
            $this->addError( APIErrors::INVALID_VSHOW_ID, $vshow_id );
            return;
        }	
        return $vshow;	
	}	
}
?>