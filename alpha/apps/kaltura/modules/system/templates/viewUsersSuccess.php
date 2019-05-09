<?php

function addRow($vuser, $even_row )
{
	
	$link = '/index.php/myvidiun/viewprofile?screenname=' .$vuser['screenname'];
	
	$s = '<tr ' . ( $even_row ? 'class="even" ' : '' ). '>'.
	 	'<td class="imgHolder"><a href="'.$link.'"><img src="'.$vuser['image'].'" alt="'.$vuser['screenname'].'" /></a></td>'.
	 	'<td class="info"><a href="'.$link.'">'.$vuser['fullname'].'</a></td>'.
	 	'<td><img src="/images/flags/'.strtolower($vuser['country']).'.gif"></td>'.
	 	'<td>'.$vuser['gender'].'</td>'.
	 	'<td>'.$vuser['createdAt'].'</td>'.
	 	'<td>'.$vuser['views'].'</td>'.
	 	'<td>'.$vuser['fans'].'</td>'.
	 	'<td>'.$vuser['shows'].'</td>'.
	 	'<td>'.$vuser['roughcuts'].'</td>'.
	 	'<td>'.$vuser['entries'].'</td>'.
	 	'<td class="action"><span class="btn" title="Delete" onclick="onClickDeleteUser('.$vuser['id'].')"></span>Delete</td>'.
	 '</tr>';
	 
	return $s;
}



function firstPage($text, $pagerHtml, $user_id , $partner_id)
{
	
	$VUSER_SORT_MOST_VIEWED = vuser::VUSER_SORT_MOST_VIEWED;
	$VUSER_SORT_MOST_RECENT = vuser::VUSER_SORT_MOST_RECENT;  
	$VUSER_SORT_NAME = vuser::VUSER_SORT_NAME;
	$VUSER_SORT_AGE = vuser::VUSER_SORT_AGE;
	$VUSER_SORT_COUNTRY = vuser::VUSER_SORT_COUNTRY;
	$VUSER_SORT_CITY = vuser::VUSER_SORT_CITY;
	$VUSER_SORT_GENDER = vuser::VUSER_SORT_GENDER;
	$VUSER_SORT_MOST_FANS = vuser::VUSER_SORT_MOST_FANS;
	$VUSER_SORT_MOST_ENTRIES = vuser::VUSER_SORT_MOST_ENTRIES;
	$VUSER_SORT_PRODUCED_VSHOWS = vuser::VUSER_SORT_PRODUCED_VSHOWS;
	
	$options = dashboardUtils::partnerOptions ( $partner_id );
	
echo <<<EOT
<script type="text/javascript">

jQuery(document).ready(function(){
	mediaSortOrder = $VUSER_SORT_MOST_VIEWED;
	var defaultMediaPageSize = 10;
	mediaPager = new ObjectPager('media', defaultMediaPageSize, requestMediaPeople );
	updatePagerAndRebind ( "media_pager" , null , requestMediaPagePeople );
	updatePagerAndRebind ( "media_pagerB" , null , requestMediaPagePeople );

}); // end document ready

</script>
	<div class="myvidiun_viewAll myvidiun_media">
		<div class="content">
			<div class="top">
				<div class="clearfix" style="margin:10px 0;">
					<ul class="pager" id="media_pager" style="float:right; margin:0;">
						$pagerHtml
					</ul>
					<select onchange="partnerSelect(this)" id="partner_id" style="float:left;">
						$options
					</select>
				</div>
			</div><!-- end top-->
			<div class="middle">	
					<table cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								<td class="resource"></td>
								<td class="info" onclick='changeMediaSortOrder(this, $VUSER_SORT_NAME)'><span>Screen Name</span></td>
								<td class="type" onclick='changeMediaSortOrder(this, $VUSER_SORT_COUNTRY)'><span>Country</span></td>
								<td class="rating" onclick='changeMediaSortOrder(this, $VUSER_SORT_GENDER)'><span>Gender</span></td>
								<td class="date" onclick='changeMediaSortOrder(this, $VUSER_SORT_MOST_RECENT)'><span>Created</span></td>
								<td class="views color2" onclick='changeMediaSortOrder(this, $VUSER_SORT_MOST_VIEWED)'><span>Views</span></td>
								<td class="views" onclick='changeMediaSortOrder(this, $VUSER_SORT_MOST_FANS)'><span>Fans</span></td>
								<td class="views" onclick='changeMediaSortOrder(this, $VUSER_SORT_PRODUCED_VSHOWS)'><span>Shows</span></td>
								<td class="date" style="width: 25px; cursor:default;">RC</td>
								<td class="views" onclick='changeMediaSortOrder(this, $VUSER_SORT_MOST_ENTRIES)'><span>Entries</span></td>
								<td class="action" >Action</td>
							</tr>
						</thead>
						<tbody id="media_content">
							$text
						</tbody>
					</table>
			</div><!-- end middle-->
			<div class="clearfix">
				<ul class="pager" id="media_pagerB">
					$pagerHtml
				</ul>
			</div>
		</div><!-- end content-->
		<div class="bgB"></div>
	</div><!-- end media-->
EOT;
}


$text = '';
$i=0;
foreach($vusersData as $vuser)
{
	$text .= addRow($vuser, ( $i % 2 == 0 ) );
	++$i;
}
	
$htmlPager = mySmartPagerRenderer::createHtmlPager( $lastPage , $page );

if ( !isset($user_id) ) $user_id=null;

if ($firstTime)
	firstPage($text, $htmlPager, $user_id , $partner_id );
else {
	$output = array(
		".currentPage" => $page,
		".maxPage" => $lastPage,
		".objectsInPage" => count($vusersData),
		".totalObjects" => $numResults,
		"media_content" => $text,
		"media_pager" => $htmlPager,
		"media_pagerB" => $htmlPager
		);
	
	echo json_encode($output);
}		

?>