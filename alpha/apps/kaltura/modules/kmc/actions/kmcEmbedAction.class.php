<?php
/**
 * @package    Core
 * @subpackage VMC
 */
class vmcEmbedAction extends vidiunAction
{
	public function execute ( ) 
	{
		$embed_code_list = array();
		
		$base_embed_code = "";
		
		$v_pl_code = "";
		$i = 0;
		do 
		{
			$embed_code = trim($this->getP ( "embed_{$i}_xml" , "" ));
			$embed_code = htmlspecialchars($embed_code);
			$embed_code_list[$i] = $embed_code;
			
			if( ! $base_embed_code )
			{
				// save the $embed_code to point to the first embed_code
				$base_embed_code  = $embed_code;
			}
			
			//$pattern = "/v_pl_[0-9]+_name=(.*)?&v_pl_[0-9]+_url=(.*)?\"/msi";
			$pattern = "/v_pl_0_name=(.*)?&v_pl_0_url=(.*)?\"/msi";
			
			$res = preg_match_all ( $pattern , $embed_code , $match );
			
			$name = @$match[1][0];
//print_r ( $name );			
			$url =  @$match[2][0];
//			print_r ( $match );

			if ( $url ) 
				$v_pl_code .= "v_pl_{$i}_name={$name}&v_pl_{$i}_url=$url&" . "\n" ;
			
			$i++;
		}
		while ( $embed_code != "" );
/*
 * 
 <object height="330" width="640" type="application/x-shockwave-flash" data="http://localhost/vwidget/wid/_1/ui_conf_id/190" id="vidiun_playlist" style="visibility: visible;">		
<param name="allowscriptaccess" value="always"/><param name="allownetworking" value="all"/><param name="bgcolor" value="#000000"/><param name="wmode" value="opaque"/><param name="allowfullscreen" value="true"/>
<param name="movie" value="http://localhost/vwidget/wid/_1/ui_conf_id/190"/>
<param name="flashvars" 
value="autoPlay=false&layoutId=playlistLight&uid=0&partner_id=1&subp_id=100&
v_pl_autoContinue=true&v_pl_autoInsertMedia=true&
v_pl_0_name=test123&v_pl_0_url=http%3A%2F%2Flocalhost%2Findex.php%2Fpartnerservices2%2Fexecuteplaylist%3Fuid%3D%26partner_id%3D1%26subp_id%3D100%26vs%3D%26format%3D8%26playlist_id%3D8nr1l9eoug"
/>
</object>

 */
		
		$this->embed_code_list = $embed_code_list;
		
		$pattern = "/v_pl_0_name=(.*)?&v_pl_0_url=([^\"]*)?/msi";
		$this->embed_merge = preg_replace( $pattern , $v_pl_code , $base_embed_code );
		sfView::SUCCESS;
	}
}
