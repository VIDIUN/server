<?php

function addRow($vshow , $allowactions, $odd)
{
	$id = $vshow['id'];
	$link = '/index.php/browse?vshow_id='.$id;
	
	$s = '<tr '.($odd ? '' : 'class="even"').'>'.
	 	'<td class="imgHolder"><a href="'.$link.'"><img src="'.$vshow['image'].'" alt="Thumbnail" /></a></td>'.
	 	'<td class="info"><a href="'.$link.'">'.$vshow['name'].'</a><br/>'.$vshow['description'].'</td>'.
	 	'<td>'.$vshow['createdAt'].'</td>'.
	 	'<td>'.$vshow['updatedAt'].'</td>'.
		'<td>'.$vshow['roughcuts'].'</td>'.	 	
	 	'<td>'.$vshow['entries'].'</td>'.
	 	'<td>'.$vshow['contributors'].'</td>'.
	 	'<td>'.$vshow['comments'].'</td>'.
	 	'<td>'.$vshow['views'].'</td>'.
	 	'<td><div class="entry_rating" title="'.$vshow['rank'].'"><div style="width:'.($vshow['rank'] * 20).'%"></div></div></td>'.
	 	( $allowactions ? '<td class="action"><span class="btn" title="Customize" onclick="onClickCustomize('.$id.')"></span><span class="btn" title="Delete" onclick="onClickDelete('.$id.')" >Delete</span></td>' : '' ).
	 '</tr>';
	 
	return $s;
}

function firstPage($text, $pagerHtml, $producer_id, $actionTD, $vidiun_part_of_flag, $screenname, $partner_id)
{
	$VSHOW_SORT_MOST_VIEWED = vshow::VSHOW_SORT_MOST_VIEWED;  
	$VSHOW_SORT_MOST_RECENT = vshow::VSHOW_SORT_MOST_RECENT;  
	$VSHOW_SORT_MOST_ENTRIES = vshow::VSHOW_SORT_MOST_ENTRIES;
	$VSHOW_SORT_NAME = vshow::VSHOW_SORT_NAME;
	$VSHOW_SORT_RANK = vshow::VSHOW_SORT_RANK;
	$VSHOW_SORT_MOST_COMMENTS = vshow::VSHOW_SORT_MOST_COMMENTS;
	$VSHOW_SORT_MOST_UPDATED = vshow::VSHOW_SORT_MOST_UPDATED;
	$VSHOW_SORT_MOST_CONTRIBUTORS = vshow::VSHOW_SORT_MOST_CONTRIBUTORS;
	
	$options = dashboardUtils::partnerOptions ( $partner_id );
	
echo <<<EOT
<script type="text/javascript">


var producer_id = 0;
var vidiun_part_of_flag = 0;

jQuery(document).ready(function(){
mediaSortOrder = $VSHOW_SORT_MOST_VIEWED;
var defaultMediaPageSize = 10;
mediaPager = new ObjectPager('media', defaultMediaPageSize, requestMedia);
updatePagerAndRebind ( "media_pager" , null , requestMediaPage );

}); // end document ready


</script>
	<div class="myvidiun_viewAll">
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
			<div class="middle clearfix">	
					<table cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								<td class="resource"></td>
								<td class="info" onclick='changeMediaSortOrder(this, $VSHOW_SORT_NAME)'><span>Vidiun Name</span></td>
								<td class="date" onclick='changeMediaSortOrder(this, $VSHOW_SORT_MOST_RECENT)'><span>Created</span></td>
								<td class="date" onclick='changeMediaSortOrder(this, $VSHOW_SORT_MOST_UPDATED)'><span>Updated</span></td>
								<td class="date" style="width: 25px">RC</td>
								<td class="entries" style="width: 40px" onclick='changeMediaSortOrder(this, $VSHOW_SORT_MOST_ENTRIES)'><span>Entries</span></td>
								<td class="date" style="width: 50px" onclick='changeMediaSortOrder(this, $VSHOW_SORT_MOST_CONTRIBUTORS)'><span>C'tors</span></td>
								<td class="date" style="width: 60px" onclick='changeMediaSortOrder(this, $VSHOW_SORT_MOST_COMMENTS)'><span>Comments</span></td>
								<td class="views color2" onclick='changeMediaSortOrder(this, $VSHOW_SORT_MOST_VIEWED)'><span>Views</span></td>
								<td class="rating" style="width: 60px" onclick='changeMediaSortOrder(this, $VSHOW_SORT_RANK)'><span>Rating</span></td>
								$actionTD
							</tr>
						</thead>
						<tbody id="media_content">
							$text
						</tbody>
					</table>
				
			</div><!-- end middle-->
		</div><!-- end content-->
		<div class="bgB"></div>
	</div><!-- end media-->
EOT;
}


if( $allowactions ) $actionTD = '<td class="action" >Action</td>'; else $actionTD = '';

$text = '';
$i = 0;
foreach($vshowsData as $vshow)
{
	$text .= addRow($vshow , $allowactions, $i);
	$i = 1 - $i;
}
	
$htmlPager = mySmartPagerRenderer::createHtmlPager( $lastPage , $page  );
			
if ($firstTime)
	firstPage($text, $htmlPager, $producer_id, $actionTD, $vidiun_part_of_flag, $screenname , $partner_id );
else {
	$output = array(
		".currentPage" => $page,
		".maxPage" => $lastPage,
		".objectsInPage" => count($vshowsData),
		".totalObjects" => $numResults,
		"media_content" => $text,
		"media_pager" => $htmlPager
		);
	
	echo json_encode($output);
}		

?>