
//alert("service_url="+vmc.vars.service_url +"\n host=" + vmc.vars.host + "\n cdn_host=" + vmc.vars.cdn_host + " \n flash_dir=" + vmc.vars.flash_dir);
// @todo:
//		* Must do / should do:
//			* Graphics:
//				* overlay
//				* replace inline styles with classes
//			* Implement embed code template from server
//			* logout() shouldn't have to location.href to '/index.php/vmc/' (should already be ok on prod)
// *		* Fix uiconf names ("Vidiun", jw playlist names) (Gonen)
//
//		* Maybe / not that important / takes too long - pushed to Blackeye:
//			* why is srvurl flashvar hardcoded to "api_v3/index.php" (why not from vconf.php)
//			* organize vmcSuccess
//			* remove openPlayer/ openPlaylist flashvars for 2.0.9
//			* move some jw code into own sub-object (doJw)
//			* memory profiling
//				* nullify preview players
//				* kill swf's - profiling
//			* understand setObjectToRemove and use or remove
//			* move cookie functions to vmc.utils
//			* get rid of legacy functions
//			* a few leftover @todo's inside code
//			* deactivate header on openning of flash modal (Eitan)
//			* In p&e, if mix, show only message box for jw (Yaron)
//			* Full copy to clipboard
//			* Flavors preview to display based on flavor size with logic for not exceeding available screen area

/* WResize: plugin for fixing the IE window resize bug (http://noteslog.com/) */
(function($){$.fn.wresize=function(f){version='1.1';wresize={fired:false,width:0};function resizeOnce(){if($.browser.msie){if(!wresize.fired){wresize.fired=true}else{var version=parseInt($.browser.version,10);wresize.fired=false;if(version<7){return false}else if(version==7){var width=$(window).width();if(width!=wresize.width){wresize.width=width;return false}}}}return true}function handleWResize(e){if(resizeOnce()){return f.apply(this,[e])}}this.each(function(){if(this==window){$(this).resize(handleWResize)}else{$(this).resize(f)}});return this}})(jQuery);

$(function(){
//	alert("dom ready:  setState("+vmc.mediator.readUrlHash()[0]+","+vmc.mediator.readUrlHash()[1]+")");
	vmc.mediator.setState(vmc.mediator.readUrlHash());
//	alert("done setState");
	vmc.utils.activateHeader(true);
//	alert("done activateHeader");

	$(window).wresize(vmc.utils.resize);
	vmc.modules.isLoadedInterval = setInterval("vmc.utils.isModuleLoaded()",200);
//	content_resize();

});

/* vmc and vmc.vars defined in script block in vmc2success.php */

	// vmc.vars.quickstart_guide = "/content/docs/pdf/VMC_Quick_Start_Guide__Butterfly.pdf#";
	vmc.vars.quickstart_guide = "/content/docs/pdf/VMC3_Quick_Start_Guide.pdf#"; // cassiopea

	vmc.functions = {
		expired : function() {
			// @todo: why no cookie killing ?
			window.location = vmc.vars.service_url + "/index.php/vmc/vmc" + location.hash; // @todo: shouldn't require '/index.php/vmc/'
		},
		doNothing : function() {
			return false;
		},
		closeEditor : function(is_modified) { // VSE
			if(is_modified) {
				var myConfirm = confirm("Exit without saving?\n\n - Click [OK] to close editor\n\n - Click [Cancel] to remain in editor\n\n");
				if(!myConfirm) {
					return;
				}
			}
			document.getElementById("flash_wrap").style.visibility = "visible";
			vidiunCloseModalBox();
		},
		saveEditor : function() { // VSE
			return;
		},
		openVcw : function(vs, conversion_profile) {
			conversion_profile = conversion_profile || "";

			// use wrap = 0 to indicate se should be open withou the html & form wrapper ????
			$("#flash_wrap").css("visibility","hidden");
			modal = vidiunInitModalBox ( null , { width: 700, height: 360 } );
			modal.innerHTML = '<div id="vcw"></div>';
			var flashvars = {
				host			: vmc.vars.host,
				cdnhost			: vmc.vars.cdn_host,
				userId			: vmc.vars.user_id,
				partnerid		: vmc.vars.partner_id,
				subPartnerId	: vmc.vars.subp_id,
				sessionId		: vmc.vars.vs,
				devFlag			: "true",
				entryId			: "-1",
				vshow_id		: "-1",
				terms_of_use	: vmc.vars.terms_of_use,
				close			: "vmc.functions.onCloseVcw",
				quick_edit		: 0, 		// "when opening from the VMC - don't add to the roughcut" ???
				vvar_conversionQuality : conversion_profile
			};

			var params = {
				allowscriptaccess: "always",
				allownetworking: "all",
				bgcolor: "#DBE3E9",
				quality: "high",
//				wmode: "opaque" ,
				movie: vmc.vars.service_url + "/vcw/ui_conf_id/" + vmc.vars.vcw_uiconf
			};

			swfobject.embedSWF(params.movie,			// old note: 36201 - new CW with ability to pass params not ready for this version
				"vcw", "680", "400" , "9.0.0", false, flashvars , params);

			setObjectToRemove("vidiun_cw"); // ???
		},
		onCloseVcw : function() {
			$("#flash_wrap").css("visibility","visible");
			vidiunCloseModalBox();
			modal = null;
			vmc.vars.vcw_open = false;
			// nullify flash object inside div vcw
		}
	}

	vmc.utils = {
		activateHeader : function(on) { // supports turning menu off if needed - just uncomment else clause
			if(on) {
//				$("a").unbind("click");
				$("a").click(function(e) {
					var go_to,
					tab = (e.target.tagName == "A") ? e.target.id : $(e.target).parent().attr("id");
//					alert("tab="+tab);
					switch(tab) {
						case "Dashboard" :
							go_to = { module : "dashboard", subtab : "" };
							break;
						case "Content" :
							go_to = { module : "content", subtab : "Manage" };
							break;
						case "Studio" :
//							go_to = { module : "appstudio", subtab : "players_list" };
//							break;
						case "Appstudio" :
							go_to = { module : "appstudio", subtab : "players_list" };
							break;
						case "Settings" :
							go_to = { module : "Settings", subtab : "Account_Settings" };
							break;
						case "Analytics" :
							go_to = { module : "reports", subtab : "Bandwidth Usage Reports" };
							break;
//						case "Advertising" :
//							go_to = "tremor";
//							break;
						case "Quickstart Guide" :
							this.href = vmc.vars.quickstart_guide;
							return true;
						case "Logout" :
							vmc.utils.logout();
							return false;
						case "Support" :
							vmc.utils.openSupport(this);
							return false;
						default :
							return false;
					}
//					console.log(go_to);
//					if(go_to == "tremor") {
//						$("#flash_wrap").html('<iframe src="http://publishers.adap.tv/osclient/" scrolling="no" frameborder="0" marginheight="0" marginwidth="0" width="100%" height="' + $("#main").height() + '"></iframe>');
//					}
//					else {
					vmc.mediator.setState(go_to);
					return false;
				});
			}
//			else {
//				$("a").unbind("click")
//					  .click(function(){
//						return false;
//					  });
//			}
		},
		openSupport : function(href) {
			vidiunCloseModalBox();
			var modal_width = $.browser.msie ? 543 : 519;
			var iframe_height = $.browser.msie ? 751 : ($.browser.safari ? 697 : 732);
			$("#flash_wrap").css("visibility","hidden");
			modal = vidiunInitModalBox ( null , { width : modal_width , height: 450 } );
			modal.innerHTML = '<div id="modal"><div id="titlebar"><a id="close" href="#close"></a>' +
							  '<b>Support Request</b></div> <div id="modal_content"><iframe id="support" src="' + href + '" scrolling="no" frameborder="0"' +
							  'marginheight="0" marginwidth="0" height="' + iframe_height + '" width="519"></iframe></div></div>';
			$("#mbContent").addClass("new");
			$("#close").click(function() {
				vmc.utils.closeModal();
				return false;
			});
			return false;
		},

		// merge multipile (unlimited) json object into one.  All arguments passed must be json object.
		// The first argument passed is the json object into which the others will be merged.
		mergeJson : function() {
			var i,
			args=arguments.length,
			primaryObject=arguments[0];
			for(var j=1; j<args ; j++) {
				var jsonObj=arguments[j];
				for(i in jsonObj) {
					primaryObject[i] = jsonObj[i];
				}
			}
			return primaryObject;
		},
		jsonToQuerystring : function(jsonObj,joiner) {
			var i,
			myString="";
			if(typeof joiner == "undefined")
				var joiner = "&";
			for(i in jsonObj) {
				myString += i + "=" + jsonObj[i] + joiner;
			}
			return myString;
		},
		logout : function() {
			var expiry = new Date("January 1, 1970"); // "Thu, 01-Jan-70 00:00:01 GMT";
			expiry = expiry.toGMTString();
			document.cookie = "pid=; expires=" + expiry + "; path=/";
			document.cookie = "subpid=; expires=" + expiry + "; path=/";
			document.cookie = "uid=; expires=" + expiry + "; path=/";
			document.cookie = "vmcvs=; expires=" + expiry + "; path=/";
			document.cookie = "screen_name=; expires=" + expiry + "; path=/";
			document.cookie = "email=; expires=" + expiry + "; path=/";
			var state = vmc.mediator.readUrlHash();
			$.ajax({
                url: location.protocol + "//" + location.hostname + "/index.php/vmc/logout",
                type: "POST",
                data: { "vs": vmc.vars.vs },
                dataType: "json",
                complete: function() {
                        window.location = vmc.vars.service_url + "/index.php/vmc/vmc#" + state.module + "|" + state.subtab;
                }
			});
		},
		copyCode : function () {
			$("#copy_msg").show();
			setTimeout(function(){$("#copy_msg").hide(500);},1500)
			$(" textarea#embed_code").select();
		},
		resize : function() {
			var doc_height = $(document).height(),
			offset = $.browser.mozilla ? 37 : 74;
			doc_height = (doc_height-offset)+"px";
			$("#flash_wrap").height(doc_height);
			$("#server_wrap iframe").height(doc_height);
		},
		escapeQuotes : function(string) {
			string = string.replace(/"/g,"&Prime;");
			string = string.replace(/'/g,"&prime;");
			return string;
		},
		isModuleLoaded : function() {
			if($("#flash_wrap object").length || $("#flash_wrap embed").length) {
				vmc.utils.resize();
//				clearInterval(flashMovieTimeout);
				clearInterval(vmc.modules.isLoadedInterval);
				vmc.modules.isLoadedInterval = null;
			}
		},
		debug : function() {
			try{
				console.info(" vs: ",vmc.vars.vs);
				console.info(" partner_id: ",vmc.vars.partner_id);
			}
			catch(err) {}
		}()

		/*,
		cookies : {
			set		: function(){},
			get		: function(){},
			kill	: function(){}
		}*/
	}
//};
	vmc.utils.closeModal = function() {
			vidiunCloseModalBox();
			$("#flash_wrap").css("visibility","visible");
			return false;
	}

	vmc.mediator =  {
		/*
		  Need to implement saveAndClose call to module before switching tabs via html click:
			- inside swf's, show confirm: Save your changes before exiting ? [Yes] [No] [Cancel]
				- Yes = save and return true to html js function (to continue with tab change)
				- No = return true to html js function (without saving)
				- Cancel = return false to html js function, thereby canceling tab change
			- currently saveandclose calls onTabChange (no need)
		*/
		setState : function(go_to) { // go_to as json { module : module, subtab : subtab  }
//			alert("setState("+go_to.module+","+go_to.subtab+")");
			if(!go_to) {
//				alert("!go_to");
				go_to = vmc.vars.next_state; // dbl... checked elsewhere
				vmc.vars.next_state = null; // ???
			}
			if(go_to.subtab == "uploadVMC") {
//				alert("open vcw");
//				vmc.functions.openVcw();
				vmc.vars.vcw_open = true;
				//openCw(vmc.vars.vs, null); // null = conversion_quality
				go_to.subtab = "Upload";
			}
			if(go_to.subtab.toLowerCase() == "publish")
				go_to.subtab = "Playlists";
			if(!vmc.vars.vcw_open) { // ???
//				alert("CloseModalBox");
				vidiunCloseModalBox();
			}
//			alert("vmc.mediator.loadModule(" + go_to.module + "," + go_to.subtab + ")");
			vmc.mediator.setTab(go_to.module);
//			alert("post setTab");
			vmc.mediator.writeUrlHash(go_to.module,go_to.subtab);
//			alert("post writeUrlHash");
//			if(navigator.userAgent.indexOf("Chrome") != -1) {
////				alert("chrome");
//				setTimeout(vmc.mediator.loadModule(go_to.module,go_to.subtab),100);
//				return;
//			}
//			else {
				vmc.mediator.loadModule(go_to.module,go_to.subtab);
//			}
//			alert("post loadModule");
		},
		loadModule : function(module,subtab) {
//	alert("loadModule("+module+","+subtab+")");
			window.vmc_module = null;	// nullify swf object - @todo: check if works/ set correctly
			module = module.toLowerCase();
			if(module=="account")
				module = "settings";
//			subtab = subtab.charAt(0).toUpperCase() + subtab.slice(1).toLowerCase();
			subtab = subtab.replace(/ /g,"%20");
			var module_url = {data : eval("vmc.modules." + module + ".swf_url")};
//	alert("module_url="+module_url.data);
			var attributes = vmc.utils.mergeJson(vmc.modules.shared.attributes,module_url);
			var flashvars = vmc.utils.mergeJson(vmc.modules.shared.flashvars,eval("vmc.modules." + module + ".flashvars"),{ subNavTab : subtab });
			flashvars = { flashvars : vmc.utils.jsonToQuerystring(flashvars) };
			var params = vmc.utils.mergeJson(vmc.modules.shared.params,flashvars);
//			params.wmode = (module == "reports") ? "window" : "opaque";
//			if(module == "settings") {
//					params.wmode = "opaque";
//			}
//			alert(params.wmode);
//	alert("swfobject.createSWF("+attributes+", "+params+", "+vcms+")");
			window.vmc_module = swfobject.createSWF(attributes, params, "vcms");
			if(vmc.vars.vcw_open) {
				$("#flash_wrap").css("visibility","hidden");
				vmc.functions.openVcw();
				vmc.vars.vcw_open = false;
			}
//			alert($("#vcms"));
		},
		writeUrlHash : function(module,subtab){
//	alert("writeUrlHash");
			if(module == "account")
				module = "Settings";
			location.hash = module + "|" + subtab;
			document.title = "VMC > " + module.charAt(0).toUpperCase() + module.slice(1) + ((subtab && subtab != "") ? " > " + subtab + " |" : "");
		},
		setTab : function(module){
			if(module == "reports") {
				module = "Analytics";
			}
			else if(module == "account"){
				module = "Settings";
			}
			else {
				module = module.substring(0,1).toUpperCase() + module.substring(1); // capitalize 1st letter
			}
			$("#vmcHeader ul li a").removeClass("active");
			$("a#" + module).addClass("active");
		},
		readUrlHash : function() {
			var module = "dashboard", // @todo: change to vmc.vars.default_state.module ?
			subtab = "";
			try {
				var hash = location.hash.split("#")[1].split("|");
			}
			catch(err) {
				var nohash=true;
//				err = null;
			}
			if(!nohash && hash[0]!="") {
				module = hash[0];
				subtab = hash[1];
			}
			return { "module" : module, "subtab" : subtab };
		},
		 selectContent : function(uiconf_id,is_playlist) { // called by selectPlaylistContent which is caled from appstudio
//			alert("selectContent("+uiconf_id+","+is_playlist+")");
			var subtab = is_playlist ? "Playlists" : "Manage";
//			vmc.vars.current_uiconf = uiconf_id; // used by doPreviewEmbed
			vmc.vars.current_uiconf = { "uiconf_id" : uiconf_id , "is_playlist" : is_playlist }; // used by doPreviewEmbed
			vmc.mediator.setState( { module : "content", subtab : subtab } );
		 }
	}

	vmc.modules = {
		shared : {
			attributes : {
				height				: "100%",
				width				: "100%"
			},
			params : {
				allowScriptAccess	: "always",
				allowNetworking		: "all",
				allowFullScreen		: "false",
				bgcolor				: "#F7F7F7",
				autoPlay			: "true"//,
//				wmode				: "opaque"
			},
			flashvars : {
				host				: vmc.vars.host,
				cdnhost				: vmc.vars.cdn_host,
				srvurl				: "api_v3/index.php",
				partnerid			: vmc.vars.partner_id,
				subpid				: vmc.vars.subp_id,
				uid					: vmc.vars.user_id,
				vs					: vmc.vars.vs,
				entryId				: "-1",
				vshowId				: "-1",
				widget_id			: "_" + vmc.vars.partner_id,
				enableCustomData	: vmc.vars.enable_custom_data,
				urchinNumber		: vmc.vars.google_analytics_account // "UA-12055206-1""
			}
		},
		dashboard : {
			swf_url : vmc.vars.flash_dir + "/vmc/dashboard/"   + vmc.vars.versions.dashboard + "/dashboard.swf",
			flashvars : {
				userName			: vmc.vars.screen_name,
				firstLogin			: vmc.vars.first_login,
				uploadDocLink		: vmc.vars.quickstart_guide + "page=3",
				embedDocLink		: vmc.vars.quickstart_guide + "page=5",
				customizeDocLink	: vmc.vars.quickstart_guide + "page=52" // bf=37
			}
		},
		content : {
			swf_url : vmc.vars.flash_dir + "/vmc/content/" + vmc.vars.versions.content + "/content.swf",
			flashvars : {
				moderationVDPVersion : "v3.3.4",
				drillDownVDPVersion  : "v3.3.4",
				moderationUiconf	: vmc.vars.content_moderate_uiconf,
				drilldownUiconf		: vmc.vars.content_drilldown_uiconf,
				refreshPlayerList	: "refreshPlayerList", // @todo: ???!!!
				refreshPlaylistList : "refreshPlaylistList", // @todo: ???!!!
				openPlayer			: "vmc.preview_embed.doPreviewEmbed", // @todo: remove for 2.0.9 ?
				openPlaylist		: "vmc.preview_embed.doPreviewEmbed",
				email				: vmc.vars.email,
				visibleCT			: vmc.vars.paying_partner,
				openCw				: "vmc.functions.openVcw",
				enableLiveStream	: vmc.vars.enable_live,
				sampleFileUrl		: "/content/docs/csv/vidiun_batch_upload_andromeda.csv",
				metadataViewUiconf	: vmc.vars.metadata_view_uiconf
			}
		},
		appstudio : {
			swf_url : vmc.vars.flash_dir + "/vmc/appstudio/" + vmc.vars.versions.appstudio + "/applicationstudio.swf",
			playlist_url :	'http%3A%2F%2F' + vmc.vars.host + '%2Findex.php%2Fpartnerservices2%2Fexecuteplaylist%3Fuid%3D%26partner_id%3D' +
							vmc.vars.partner_id + '%26subp_id%3D' +  vmc.vars.partner_id + '00%26format%3D8%26vs%3D%7Bvs%7D%26playlist_id%3D',
			flashvars : {
				entryId					: vmc.vars.appStudioExampleEntry ,
				"playlistAPI.vpl0Name"	: "playlist1",
				"playlistAPI.vpl0Url"	: '',
				"playlistAPI.vpl1Name"	: "playlist2",
				"playlistAPI.vpl1Url"	: '',
				inapplicationstudio		: "true",
				Appstudiouiconfid		: vmc.vars.appstudio_uiconfid,
				//vdpUrl					: vmc.vars.flash_dir + "/vdp3/v3.3.4/vdp3.swf",
				servicesPath			: "index.php/partnerservices2/",
				serverPath				: "http://"+vmc.vars.host,
				partner_id				: vmc.vars.partner_id,
				subp_id					: vmc.vars.subp_id,
				templatesXmlUrl			: vmc.vars.appstudio_templatesXmlUrl || "",
				enableAds				: vmc.vars.enableAds,
				enableCustomData		: vmc.vars.enable_custom_data
//				widget_id				: "_" + vmc.vars.partner_id
			}
		},
		settings : { // formerly "account""
			swf_url : vmc.vars.flash_dir + "/vmc/account/"   + vmc.vars.versions.account + "/account.swf",
			flashvars: {
				email				: vmc.vars.email,
				showUsage			: vmc.vars.show_usage
			}
		},
		reports : {
			swf_url : vmc.vars.flash_dir + "/vmc/analytics/"   + vmc.vars.versions.reports + "/ReportsAndAnalytics.swf",
			flashvars : {
				drillDownVdpVersion	: "v3.3.4",
				drillDownVdpUiconf	: vmc.vars.reports_drilldown,
				serverPath			: vmc.vars.service_url
			}
		}
	}
	vmc.utils.mergeJson(vmc.modules.appstudio.flashvars,{ "playlistAPI.vpl0Url"	: vmc.modules.appstudio.playlist_url + vmc.vars.appStudioExamplePlayList0, "playlistAPI.vpl1Url" : vmc.modules.appstudio.playlist_url + vmc.vars.appStudioExamplePlayList1 });
//	vmc.modules.studio = vmc.modules.appstudio;

	vmc.preview_embed = {

		// called from p&e dropdown, from content.swf and from appstudio.swf
		doPreviewEmbed : function(id, name, description, is_playlist, uiconf_id, live_bitrates) {
		// entry/playlist id, description, true/ false (or nothing or "" or null), uiconf id, live_bitrates obj or boolean, is_mix
//			alert("doPreviewEmbed: id="+id+", name="+name+", description="+description+", is_playlist="+is_playlist+", uiconf_id="+uiconf_id);

			if(id != "multitab_playlist") {

				name = vmc.utils.escapeQuotes(name);
				description = vmc.utils.escapeQuotes(description); // @todo: move to "// JW" block

				if(vmc.vars.current_uiconf) { // set by vmc.mediator.selectContent called from appstudio's "select content" action
//					console.log(vmc.vars.current_uiconf); alert("vmc.vars.current_uiconf logged");
//					console.log("is_playlist=",is_playlist);
					if((is_playlist && vmc.vars.current_uiconf.is_playlist) || (!is_playlist && !vmc.vars.current_uiconf.is_playlist)) { // @todo: minor optimization possible
						var uiconf_id = vmc.vars.current_uiconf.uiconf_id;
//						alert("doPreviewEmbed says:\nvmc.vars.current_uiconf true -> uiconf_id = "+uiconf_id);
					}
					vmc.vars.current_uiconf = null;
				}

				if(!uiconf_id) { // get default uiconf_id (first one in list)
					var uiconf_id = is_playlist ? vmc.vars.playlists_list[0].id : vmc.vars.players_list[0].id;
	//				alert(uiconf_id);
				}

				if(uiconf_id > 899 && uiconf_id < 1000) {
					vmc.vars.silverlight = true;
				}
				// JW
				else if(uiconf_id > 799 && uiconf_id < 900) {
					vmc.vars.jw = true,
					jw_license_html = '<strong>COMMERCIAL</strong>',
					jw_options_html = '',
					jw_nomix_box_html = vmc.preview_embed.jw.showNoMix(false,"check");

					if(vmc.vars.jw_swf == "non-commercial.swf") {
						jw_license_html =   '<a href="http://creativecommons.org/licenses/by-nc-sa/3.0/" target="_blank" class="license tooltip"' +
											'title="With this license your player will show a JW Player watermark.  You may NOT use the non-commercial' +
											'JW Player on commercial sites such as: sites owned or operated by corporations, sites with advertisements,' +
											'sites designed to promote a product, service or brand, etc.  If you are not sure whether you need to '+
											'purchase a license, contact us.  You also may not use the AdSolution monetization plugin ' +
											'(which lets you make money off your player).">NON-COMMERCIAL <img src="http://corp.vidiun.com/images/graphics/info.png" alt="show tooltip" />' +
											'</a>&nbsp;&bull;&nbsp;<a href="http://corp.vidiun.com/about/contact?subject=JW%20Player%20to%20commercial%20license&amp;' +
											'&amp;pid=' + vmc.vars.partner_id + '&amp;name=' + vmc.vars.screen_name + '&amp;email=' + vmc.vars.email  + '" target="_blank" class="license tooltip" ' +
											'title="Go to the Contact Us page and call us or fill in our Contact form and we\'ll call you (opens in new window/ tab).">Upgrade ' +
											'<img src="http://corp.vidiun.com/images/graphics/info.png" alt="show tooltip" /></a>';
						var jw_license_ads_html = '<li>Requires <a href="http://corp.vidiun.com/about/contact?subject=JW%20Player%20to%20commercial%20license&amp;" ' +
											  'class="tooltip" title="With a Commercial license your player will not show the JW Player watermark and you will be ' +
											  'allowed to use the player on any site you want as well as use AdSolution (which lets you make money off your player)."' +
											  'target="_blank">Commercial license <img src="http://corp.vidiun.com/images/graphics/info.png" alt="show tooltip" /></a></li>';
					}
					jw_options_html =	'<div class="label">License Type:</div>\n<div class="description">' + jw_license_html + '</div>\n' +
										'<div class="label">AdSolution:</div><div class="description"> <input type="checkbox" id="AdSolution" ' +
										'onclick="vmc.preview_embed.jw.adSolution()" onmousedown="vmc.vars.jw_chkbox_flag=true" /> Enable ads ' +
										'in your videos.&nbsp; <a href="http://www.longtailvideo.com/referral.aspx?page=vidiun&ref=azbkefsfkqchorl" ' +
										'target="_blank" class="tooltip" title="Go to the JW website to sign up for FREE or to learn more about ' +
										'running in-stream ads in your player from Google AdSense for Video, ScanScout, YuMe and others. (opens ' +
										'in new window/ tab)"> Free sign up... <img src="http://corp.vidiun.com/images/graphics/info.png" alt="' +
										'show tooltip" /></a><br />\n <ul id="ads_notes">\n  <li>Channel Code: <input onblur="' +
										'vmc.preview_embed.jw.adsChannel(this, \'' + id + '\', \'' + name + '\', \'' + description + '\', ' + (is_playlist || false) + ', \'' + uiconf_id + '\');" ' +
										'type="text" id="adSolution_channel" value="" /> <button>Apply</button></li>\n' + (jw_license_ads_html || '') +
										'\n </ul>\n </div>\n';
				} // END JW
			} // end !multitab_playlist

			var embed_code, preview_player,
			id_type = is_playlist ? "Playlist " + (id == "multitab_playlist" ? "Name" : "ID") : "Entry ID",
			uiconf_details = vmc.preview_embed.getUiconfDetails(uiconf_id,is_playlist);
//			console.log("uiconf_details="+uiconf_details);
			if(vmc.vars.jw) {
				embed_code = vmc.preview_embed.jw.buildJWEmbed(id, name, description, is_playlist, uiconf_id);
				preview_player = embed_code.replace('flvclipper', 'flvclipper/vs/' + vmc.vars.vs);
			}
			else if(vmc.vars.silverlight) {
				embed_code = vmc.preview_embed.buildSilverlightEmbed(id, name, is_playlist, uiconf_id);
				preview_player = embed_code.replace('{VS}','vs=' + vmc.vars.vs);
				embed_code = embed_code.replace('{VS}','');
				embed_code = embed_code.replace("{ALT}", ((vmc.vars.whitelabel) ? "" : "<br/>" + vmc.preview_embed.embed_code_template.vidiun_links));
			}
			else {
				embed_code = vmc.preview_embed.buildVidiunEmbed(id, name, description, is_playlist, uiconf_id);
				preview_player = embed_code.replace('{FLAVOR}','vs=' + vmc.vars.vs + '&');
				embed_code = embed_code.replace('{FLAVOR}','');
			}
			var modal_html = '<div id="modal"><div id="titlebar"><a id="close" href="#close"></a>' +
							 '<a id="help" target="_blank" href="' + vmc.vars.service_url + '/index.php/vmc/help#contentSection118"></a>' + id_type +
							 ': ' + id + '</div> <div id="modal_content">' +
							 ((typeof live_bitrates == "object") ? vmc.preview_embed.buildLiveBitrates(name,live_bitrates) : '') +
//							 ((id == "multitab_playlist") ? '' : vmc.preview_embed.buildSelect(id, name, description, is_playlist, uiconf_id)) +
							 ((id == "multitab_playlist") ? '' : vmc.preview_embed.buildSelect(is_playlist, uiconf_id)) +
							 (vmc.vars.jw ? jw_nomix_box_html : '') +
							 '<div id="player_wrap">' + preview_player + '</div>' +
							 (vmc.vars.jw ? jw_options_html : '') +
							 ((vmc.vars.silverlight || live_bitrates) ? '' : vmc.preview_embed.buildRtmpOptions()) +
							 '<div class="label">Embed Code:</div> <textarea id="embed_code" rows="5" cols=""' +
							 'readonly="true" style="width:' + (parseInt(uiconf_details.width)-10) + 'px;">' + embed_code + '</textarea>' +
							 '<div id="copy_msg">Press Ctrl+C to copy embed code (Command+C on Mac)</div><button id="select_code">' +
							 '<span>Select Code</span></button></div></div>';
//			alert(modal_html);
			vmc.vars.jw = false;
			vmc.vars.silverlight = false;

			vidiunCloseModalBox();
			$("#flash_wrap").css("visibility","hidden");
			modal = vidiunInitModalBox ( null , { width : parseInt(uiconf_details.width) + 20 , height: parseInt(uiconf_details.height) + 200 } );
			modal.innerHTML = modal_html;
			$("#mbContent").addClass("new");
			// attach events here instead of writing them inline
			$("#embed_code, #select_code").click(function(){
				vmc.utils.copyCode();
			});
			$("#close").click(function(){
				 vmc.utils.closeModal();
				 return false;
			});
			$("#delivery_type").change(function(){
				vmc.vars.embed_code_delivery_type = this.value;
				vmc.preview_embed.doPreviewEmbed(id, name, description, is_playlist, uiconf_id);
			});
			$("#player_select").change(function(){
				vmc.preview_embed.doPreviewEmbed(id, name, description, is_playlist, this.value, live_bitrates);
			});
		}, // doPreviewEmbed

		buildLiveBitrates : function(name,live_bitrates) {
			var bitrates = "",
			len = live_bitrates.length,
			i;
			for(i=0;i<len;i++) {
				bitrates += live_bitrates[i].bitrate + " kbps, " + live_bitrates[i].width + " x " + live_bitrates[i].height + "<br />";
			}
			var lbr_data = 	'<dl style="margin: 0 0 15px">' + '<dt>Name:</dt><dd>' + name + '</dd>' +
							'<dt>Bitrates:</dt><dd>' + bitrates + '</dd></dl>';
			return lbr_data;
		},

		buildSilverlightEmbed : function(id, name, is_playlist, uiconf_id) {
			var uiconf_details = vmc.preview_embed.getUiconfDetails(uiconf_id,is_playlist),
			cache_st = vmc.preview_embed.setCacheStartTime(),
			embed_code = vmc.preview_embed.embed_code_template.silverlight;
			embed_code = embed_code.replace("{WIDTH}",uiconf_details.width);
			embed_code = embed_code.replace("{HEIGHT}",uiconf_details.height);
			embed_code = embed_code.replace("{HOST}",vmc.vars.host);
			embed_code = embed_code.replace("{CACHE_ST}",cache_st);
			embed_code = embed_code.replace("{VER}",uiconf_details.swfUrlVersion); // sl
			embed_code = embed_code.replace("{PARTNER_ID}",vmc.vars.partner_id);
//			embed_code = embed_code.replace("{CDN_HOST}",vmc.vars.cdn_host); // sl
			embed_code = embed_code.replace("{UICONF_ID}",uiconf_id);
			embed_code = embed_code.replace(/{SILVERLIGHT}/gi,uiconf_details.minRuntimeVersion);
			embed_code = embed_code.replace("{INIT_PARAMS}",(is_playlist ? "playlist_id" : "entry_id"));
			embed_code = embed_code.replace("{ENTRY_ID}", id);
			return embed_code;
		},
		buildRtmpOptions : function() {
			var selected = ' selected="selected"';
			var delivery_type = vmc.vars.embed_code_delivery_type || "http";
			var html = '<div id="rtmp" class="label">Delivery Type:</div> <select id="delivery_type">';
			var options = '<option value="http"' + ((delivery_type == "http") ? selected : "") + '>Progressive Download (HTTP)&nbsp;</option>' +
						  '<option value="rtmp"' + ((delivery_type == "rtmp") ? selected : "") + '>Adaptive Streaming (RTMP)&nbsp;</option>';
			html += options + '</select>';
			return html;
		},

		// for content|Manage->drilldown->flavors->preview
		// flavor_details = json:
		doFlavorPreview : function(entry_id, entry_name, flavor_details) {
//			console.log(flavor_details);
//			alert("doFlavorPreview(entry_id="+entry_id+", entry_name="+entry_name+", flavor_details logged)");
			entry_name = vmc.utils.escapeQuotes(entry_name);
//			var flavor_asset_name = vmc.utils.escapeQuotes(flavor_details.flavor_name) || "unknown";
			vidiunCloseModalBox();
			$("#flash_wrap").css("visibility","hidden");
			modal = vidiunInitModalBox ( null , { width : parseInt(vmc.vars.default_vdp.width) + 20 , height: parseInt(vmc.vars.default_vdp.height) + 10 } );
			$("#mbContent").addClass("new");
			var player_code = vmc.preview_embed.buildVidiunEmbed(entry_id,entry_name,null,false,vmc.vars.default_vdp);
//			alert("flavor_details.asset_id="+flavor_details.asset_id);
			player_code = player_code.replace('&{FLAVOR}', '&flavorId=' + flavor_details.asset_id + '&vs=' + vmc.vars.vs);
			var modal_html = '<div id="modal"><div id="titlebar"><a id="close" href="#close"></a>' +
							 'Flavor Preview</div>' +
							 '<div id="modal_content">' + player_code + '<dl>' +
							 '<dt>Entry Name:</dt><dd>&nbsp;' + entry_name + '</dd>' +
							 '<dt>Entry Id:</dt><dd>&nbsp;' + entry_id + '</dd>' +
							 '<dt>Flavor Name:</dt><dd>&nbsp;' + flavor_details.flavor_name + '</dd>' +
							 '<dt>Flavor Asset Id:</dt><dd>&nbsp;' + flavor_details.asset_id + '</dd>' +
							 '<dt>Bitrate:</dt><dd>&nbsp;' + flavor_details.bitrate + '</dd>' +
							 '<dt>Codec:</dt><dd>&nbsp;' + flavor_details.codec + '</dd>' +
							 '<dt>Dimensions:</dt><dd>&nbsp;' + flavor_details.dimensions.width + ' x ' + flavor_details.dimensions.height + '</dd>' +
							 '<dt>Format:</dt><dd>&nbsp;' + flavor_details.format + '</dd>' +
							 '<dt>Size (KB):</dt><dd>&nbsp;' + flavor_details.sizeKB + '</dd>' +
							 '<dt>Status:</dt><dd>&nbsp;' + flavor_details.status + '</dd>' +
							 '</dl></div></div>';
			modal.innerHTML = modal_html;
			$("#close").click(function(){
				 vmc.utils.closeModal();
				 return false;
			});
		},

		// eventually replace with <? php echo $embedCodeTemplate; ?>  ;  (variables used: HEIGHT WIDTH HOST CACHE_ST UICONF_ID PARTNER_ID PLAYLIST_ID ENTRY_ID) + {VER}, {SILVERLIGHT}, {INIT_PARAMS} for Silverlight + NAME, DESCRIPTION
		embed_code_template :	{
			object_tag :	'<object id="vidiun_player" name="vidiun_player" type="application/x-shockwave-flash" allowFullScreen="true" ' +
							'allowNetworking="all" allowScriptAccess="always" height="{HEIGHT}" width="{WIDTH}" ' +
							'xmlns:dc="http://purl.org/dc/terms/" xmlns:media="http://search.yahoo.com/searchmonkey/media/" rel="media:{MEDIA}" ' +
							'resource="http://{HOST}/index.php/vwidget/cache_st/{CACHE_ST}/wid/_{PARTNER_ID}/uiconf_id/{UICONF_ID}{ENTRY_ID}" ' +
							'data="http://{HOST}/index.php/vwidget/cache_st/{CACHE_ST}/wid/_{PARTNER_ID}/uiconf_id/{UICONF_ID}{ENTRY_ID}">' +
							'<param name="allowFullScreen" value="true" /><param name="allowNetworking" value="all" />' +
							'<param name="allowScriptAccess" value="always" /><param name="bgcolor" value="#000000" />' +
							'<param name="flashVars" value="{FLASHVARS}&{FLAVOR}" /><param name="movie" value="http://{HOST}/index.php/vwidget' +
							'/cache_st/{CACHE_ST}/wid/_{PARTNER_ID}/uiconf_id/{UICONF_ID}{ENTRY_ID}" />{ALT} {SEO} ' + '</object>',
			playlist_flashvars :	'playlistAPI.autoInsert=true&playlistAPI.vpl0Name={PL_NAME}' +
									'&playlistAPI.vpl0Url=http%3A%2F%2F{HOST}%2Findex.php%2Fpartnerservices2%2Fexecuteplaylist%3Fuid%3D%26' +
									'partner_id%3D{PARTNER_ID}%26subp_id%3D{PARTNER_ID}00%26format%3D8%26vs%3D%7Bvs%7D%26playlist_id%3D{PLAYLIST_ID}',
			vidiun_links :		'<a href="http://corp.vidiun.com">video platform</a> <a href="http://corp.vidiun.com/video_platform/video_management">' +
								'video management</a> <a href="http://corp.vidiun.com/solutions/video_solution">video solutions</a> ' +
								'<a href="http://corp.vidiun.com/video_platform/video_publishing">video player</a>',
			media_seo_info :	'<a rel="media:thumbnail" href="http://{CDN_HOST}/p/{PARTNER_ID}/sp/{PARTNER_ID}00/thumbnail{ENTRY_ID}/width/120/height/90/bgcolor/000000/type/2" /> ' +
								'<span property="dc:description" content="{DESCRIPTION}" /><span property="media:title" content="{NAME}" /> ' +
								'<span property="media:width" content="{WIDTH}" /><span property="media:height" content="{HEIGHT}" /> ' +
								'<span property="media:type" content="application/x-shockwave-flash" />',
							// removed <span property="media:duration" content="{DURATION}" />
			// (variables used: {WIDTH} {HEIGHT} {HOST} {CDN_HOST} {UICONF_ID} {VER} {ENTRY_ID} {SILVERLIGHT} + Missing id and name
			silverlight :	'<object data="data:application/x-silverlight-2," type="application/x-silverlight-2" width="{WIDTH}" height="{HEIGHT}"> <param name="source" ' +
							'value="http://{HOST}/index.php/vwidget/cache_st/{CACHE_ST}/wid/_{PARTNER_ID}/uiconf_id/{UICONF_ID}/nowrapper/1/a/a.xap" />' +
//							'value="http://{HOST}/flash/slp/v{VER}/VidiunPlayer.xap?widget_id=_{PARTNER_ID}&host={HOST}&cdnHost={CDN_HOST}&uiconf_id={UICONF_ID}" />' +
							' <param name="enableHtmlAccess" value="true" /> <param name="background" value="black" /> <param name="minRuntimeVersion" ' +
							'value="{SILVERLIGHT}" /> <param name="autoUpgrade" value="true" /> <param name="InitParams" value="{INIT_PARAMS}={ENTRY_ID}&{VS}" />' +
							' <a href="http://go.microsoft.com/fwlink/?LinkId=149156&v={SILVERLIGHT}"><img src="http://go.microsoft.com/fwlink/?LinkId=161376" ' +
							'alt="Get Microsoft Silverlight" /></a>{ALT}</object>'
		},

		// id = entry id, asset id or playlist id; name = entry name or playlist name;
		// uiconf = uiconfid (normal scenario) or uiconf details json (for #content|Manage->drill down->flavors->preview)
		buildVidiunEmbed : function(id, name, description, is_playlist, uiconf) {
//		alert("buildVidiunEmbed(id="+id+", name="+name+", is_playlist="+is_playlist+", uiconf = " + uiconf);
			var uiconf_id = uiconf.uiconf_id || uiconf,
			uiconf_details = (typeof uiconf == "object") ? uiconf : vmc.preview_embed.getUiconfDetails(uiconf_id,is_playlist),  // getUiconfDetails returns json
			cache_st = vmc.preview_embed.setCacheStartTime(),
			embed_code;
//			console.log(uiconf_details); alert("uiconf_details logged");
//			alert("cache_st = " + cache_st);
			embed_code = vmc.preview_embed.embed_code_template.object_tag;
			if(!vmc.vars.jw) { // more efficient to add "&& !vmc.vars.silverlight" (?)
				vmc.vars.embed_code_delivery_type = vmc.vars.embed_code_delivery_type || "http";
				if(vmc.vars.embed_code_delivery_type == "rtmp") {
					embed_code = embed_code.replace("{FLASHVARS}", "streamerType=rtmp&amp;streamerUrl=" + vmc.vars.rtmp_host + "&amp;rtmpFlavors=1&{FLASHVARS}"); // rtmp://rtmpakmi.vidiun.com/ondemand
				}
			}
			if(is_playlist && id != "multitab_playlist") {	// playlist (not multitab)
				embed_code = embed_code.replace(/{ENTRY_ID}/g,"");
				embed_code = embed_code.replace("{FLASHVARS}",vmc.preview_embed.embed_code_template.playlist_flashvars);
//				console.log(uiconf_details.swf_version); alert("uiconf_details.swf_version logged");
				if(uiconf_details.swf_version.indexOf("v3") == -1) { // not vdp3
					embed_code = embed_code.replace("playlistAPI.autoContinue","v_pl_autoContinue");
					embed_code = embed_code.replace("playlistAPI.autoInsert","v_pl_autoInsertMedia");
					embed_code = embed_code.replace("playlistAPI.vpl0Name","v_pl_0_name");
					embed_code = embed_code.replace("playlistAPI.vpl0Url","v_pl_0_url");
				}
			}
			else {											// player and multitab playlist
				embed_code = embed_code.replace("{SEO}", (is_playlist ? "" : vmc.preview_embed.embed_code_template.media_seo_info));
				embed_code = embed_code.replace(/{ENTRY_ID}/g, (is_playlist ? "" : "/entry_id/" + id));
				embed_code = embed_code.replace("{FLASHVARS}", "");
			}
			
			embed_code = embed_code.replace("{MEDIA}", "video");	// to be replaced by real media type once doPreviewEmbed (called from within VMC>Content) starts passing full entry object			embed_code = embed_code.replace(/{ENTRY_ID}/gi, (is_playlist ? "-1" : id));
			embed_code = embed_code.replace(/{HEIGHT}/gi,uiconf_details.height);
			embed_code = embed_code.replace(/{WIDTH}/gi,uiconf_details.width);
			embed_code = embed_code.replace(/{HOST}/gi,vmc.vars.host);
			embed_code = embed_code.replace(/{CACHE_ST}/gi,cache_st);
			embed_code = embed_code.replace(/{UICONF_ID}/gi,uiconf_id);
			embed_code = embed_code.replace(/{PARTNER_ID}/gi,vmc.vars.partner_id);
			embed_code = embed_code.replace("{PLAYLIST_ID}",id);
			embed_code = embed_code.replace("{PL_NAME}",name);
   			embed_code = embed_code.replace(/{SERVICE_URL}/gi,vmc.vars.service_url);
			embed_code = embed_code.replace("{ALT}", ((vmc.vars.whitelabel) ? "" : vmc.preview_embed.embed_code_template.vidiun_links));
			embed_code = embed_code.replace("{CDN_HOST}",vmc.vars.cdn_host);
			embed_code = embed_code.replace("{NAME}", name);
			embed_code = embed_code.replace("{DESCRIPTION}", description);
//			embed_code = embed_code.replace("{DURATION}", entry.duration || '');
//			alert("embed_code: "+embed_code);
			return embed_code;
		},
//		buildSelect : function(id, name, description, is_playlist, uiconf_id) { // called from modal_html;
		buildSelect : function(is_playlist, uiconf_id) { // called from modal_html;
			// used = uiconf_id; is_playlist;
//			alert("buildSelect("+id+", "+name+", "+description+", "+is_playlist+", "+uiconf_id+")");
			uiconf_id = vmc.vars.current_uiconf || uiconf_id;  // @todo: need to nullify vmc.vars.current_uiconf somewhere... on very next line ?
			var list_type = is_playlist ? "playlist" : "player",
			list_length = eval("vmc.vars." + list_type + "s_list.length"),
			html_select = '',
			this_uiconf, selected;
//			alert("uiconf_id="+uiconf_id+" | list_type="+list_type+" | html_select ="+html_select+" | list_length ="+list_length);
			for(var i=0; i<list_length; i++) {
				this_uiconf = eval("vmc.vars." + list_type + "s_list[" + i + "]"),
				selected = (this_uiconf.id == uiconf_id) ? ' selected="selected"' : '';
				html_select += '<option ' + selected + ' value="' + this_uiconf.id + '">' + this_uiconf.name + '</option>';
			}
//			html_select = '<select onchange="vmc.preview_embed.doPreviewEmbed(\'' + id + '\',\'' + name + '\',\'' + description + '\',' + is_playlist + ', this.value)">'
			html_select = '<select id="player_select">' + html_select + '</select>';
			vmc.vars.current_uiconf = null;
			return html_select;
		},

//		reload : function(id, name, description, is_playlist, uiconf_id) {
//			var embed_code = vmc.preview_embed.buildEmbed(id, name, description, is_playlist, uiconf_id);
//			$("#player_wrap").html(embed_code);
//			$("#embed_code textarea").val(embed_code);
//			vmc.preview_embed.doPreviewEmbed(id, name, description, is_playlist, uiconf_id);
//		},

		getUiconfDetails : function(uiconf_id,is_playlist) {
//			alert("getUiconfDetails("+"uiconf_id="+uiconf_id+", +is_playlist="+is_playlist+")");
			var i,
			uiconfs_array = is_playlist ? vmc.vars.playlists_list : vmc.vars.players_list;
			for(i in uiconfs_array) {
				if(uiconfs_array[i].id == uiconf_id) {
					return uiconfs_array[i];
					break;
				}
			}
			alert("getUiconfDetails error: uiconf_id "+uiconf_id+" not found in " + ((is_playlist) ? "vmc.vars.playlists_list" : "vmc.vars.players_list"));
			return false;
		},
		setCacheStartTime : function() {
            var d = new Date;
            cache_st = Math.floor(d.getTime() / 1000) + (15 * 60); // start caching in 15 minutes
            return cache_st;
		},
		updateList : function(is_playlist) {
//			alert("updateList(" + is_playlist + ")");
			var type = is_playlist ? "playlist" : "player";
//			alert("type = " + type);
			$.ajax({
				url: vmc.vars.getuiconfs_url,
				type: "POST",
				data: { "type": type, "partner_id": vmc.vars.partner_id, "vs": vmc.vars.vs },
				dataType: "json",
				success: function(data) {
//					alert(data);
					if (data && data.length) {
//						alert("success: data && data.length");
						if(is_playlist) {
//							alert("success: vmc.vars.playlists_list = data");
							vmc.vars.playlists_list = data;
						}
						else {
//							alert("success: vmc.vars.players_list = data");
							vmc.vars.players_list = data;
						}
					}
				}
			});
		},
		// JW
		jw : {
			// @todo: chg function name to ?
			adSolution		: function() {	// checkbox onclick; @todo: change id's ?
				if ($("#AdSolution").attr("checked")) {
					$("#ads_notes").show();
					$("#adSolution_channel").focus();
				}
				else {
					$("div.description ul").hide();
					$("#adSolution_channel").val("");
				}
				vmc.vars.jw_chkbox_flag=false;
			},
			adsChannel		: function(this_input, id, name, description, is_playlist, uiconf_id) {
				if(this_input.value=="" || this_input.value=="_") {
					if (!vmc.vars.jw_chkbox_flag) {
						$("#AdSolution").attr("checked",false);
					}
					$("div.description ul").hide();
				}
				var embed_code = vmc.preview_embed.jw.buildJWEmbed(id, name, description, is_playlist, uiconf_id);
				$("#player_wrap").html(embed_code);
				$("#embed_code textarea").val(embed_code);
				// @todo: improve ux by only reloading if actual change took place
			},
			adsolutionSetup	: function(start) { // @todo: explain
				var $adSolution_channel = $("#adSolution_channel");
				if(start)
					if($adSolution_channel.val()=="")
						$adSolution_channel.val("_");
				else
					if($adSolution_channel.val()=="_")
						$adSolution_channel.val("");
			},
			showNoMix : function(checkbox,action) {
				if(checkbox) {
					if($(checkbox).is(':checked'))
						action = "set";
					else
						action = "delete"
				}
				switch(action) {
					case "set" :
						document.cookie = "vmc_preview_show_nomix_box=true; ; path=/";
						$("#nomix_box").hide(250);
						break;
					case "delete" :
						document.cookie = "vmc_preview_show_nomix_box=true; expires=Sun, 01 Jan 2000 00:00:01 GMT; path=/";
						break;
					case "check" :
						if (document.cookie.indexOf("vmc_preview_show_nomix_box") == -1)
							var html =	'<div id="nomix_box"><p><strong>NOTE</strong>: ' +
										'The JW Player does not work with Vidiun <dfn title="A Video Mix is a video made up of two or more ' +
										'Entries, normally created through the Vidiun Editor.">Video Mixes</dfn>.</p>\n<div><input type="' +
										'checkbox" onclick="vmc.preview_embed.jw.showNoMix(this)"> Don\'t show this message again.</div></div>\n';
						else
							var html =	'';
						break;
					default :
						alert("error: no action");
						return;
				}
				return html;
			},

			buildJWEmbed : function (entry_id, name, description, is_playlist, uiconf_id) {
				var uiconf_details = vmc.preview_embed.getUiconfDetails(uiconf_id,is_playlist); // @ todo: change to embed_code.
				 var width			= uiconf_details.width;
				 var height			= uiconf_details.height;
				 var playlist_type	= uiconf_details.playlistType;
				 var share			= uiconf_details.share;
				 var skin			= uiconf_details.skin;
				var jw_flashvars = '';
				var unique_id = new Date(); unique_id = unique_id.getTime();
				var jw_plugins =  new Array();

				if(!is_playlist || is_playlist == "undefined") {
					jw_flashvars += 'file=http://' + vmc.vars.cdn_host + '/p/' + vmc.vars.partner_id + '/sp/' + vmc.vars.partner_id +
									'00/flvclipper/entry_id/' + entry_id + '/version/100000/ext/flv';
					jw_plugins.push("vidiunstats");
				}
				else {
					jw_flashvars += 'file=http://' + vmc.vars.cdn_host + '/index.php/partnerservices2/executeplaylist%3Fuid%3D%26format%3D8%26playlist_id%3D' +
									entry_id + '%26partner_id%3D' + vmc.vars.partner_id + '%26subp_id%3D' + vmc.vars.partner_id + '00%26vs%3D%7Bvs%7D' +
									'&playlist=' + playlist_type;
					if(playlist_type != "bottom") {
						jw_flashvars += '&playlistsize=300';
					}
				}

				if(share == "true" || share == true) {
					jw_flashvars += '&viral.functions=embed,link&viral.onpause=false';
					jw_plugins.push("viral-2");
				}

			/* for AdSolution */
				var jw_ads = { channel : $("#adSolution_channel").val() };
				if ($("#AdSolution").is(":checked") && jw_ads.channel != "") {
					jw_ads.flashvars =	'&ltas.cc=' + jw_ads.channel + 	// &ltas.xmlprefix=http://zo.longtailvideo.com.s3.amazonaws.com/ //uacbirxmcnulxmf
										'&mediaid=' + entry_id;
					jw_plugins.push("ltas");
					jw_ads.flashvars += "&title=" + name + "&description=" + description;
					jw_flashvars += jw_ads.flashvars;
				}
			/* end AdSolution */

				var jw_skin = (skin == "undefined" || skin == "") ? '' : '&skin=http://' + vmc.vars.cdn_host + '/flash/jw/skins/' + skin;

				jw_flashvars =  jw_flashvars +
								'&amp;image=http://' + vmc.vars.cdn_host + '/p/' + vmc.vars.partner_id + '/sp/' + vmc.vars.partner_id +
								'00/thumbnail/entry_id/' + entry_id + '/width/640/height/480' + jw_skin + '&widgetId=jw00000001&entryId=' +
								entry_id + '&partnerId=' + vmc.vars.partner_id + '&uiconfId=' + uiconf_id + '&plugins=' + jw_plugins;

				var jw_embed_code = '<div id="jw_wrap_' + unique_id + '"> <object width="' + width + '" height="' + height + '" id="jw_player_' +
									unique_id + '" name="jw_player_' + unique_id + '">' +
									' <param name="movie" value="http://' + vmc.vars.cdn_host + '/flash/jw/player/' + vmc.vars.jw_swf + '" />' +
//									' <param name="wmode" value="transparent" />' +
									' <param name="allowScriptAccess" value="always" />' +
									' <param name="flashvars" value="' + jw_flashvars + '" />' +
									' <embed id="jw_player__' + unique_id + '" name="jw_player__' + unique_id + '" src="http://' +
									vmc.vars.cdn_host + '/flash/jw/player/' + vmc.vars.jw_swf + '" width="' + width + '" height="' + height +
									'" allowfullscreen="true" ' +
									'wmode="transparent" ' +
									'allowscriptaccess="always" ' + 'flashvars="' + jw_flashvars +
									'" /> <noembed><a href="http://www.vidiun.org/">Open Source Video</a></noembed> </object> </div>';
				return jw_embed_code;
			} /* end build jw embed code */
		} // END JW
	}

	vmc.editors = {
		start: function(entry_id, entry_name, editor_type, new_mix) {
//			alert("vmc.editors.start("+entry_id+","+entry_name+","+editor_type+","+new_mix+")");
			if(new_mix) {
//				alert("call create mix ajax");
//				$("body").css("cursor","wait");
				jQuery.ajax({
					url: vmc.vars.createmix_url,
					type: "POST",
					data: { "entry_id": entry_id, "entry_name": entry_name, "partner_id": vmc.vars.partner_id, "vs": vmc.vars.vs, "editor_type": editor_type, "user_id": vmc.vars.user_id },
//						dataType: "json",
					success: function(data) {
//							alert("ajax success: " + data);
						if (data && data.length) {
//								console.info(data);
//								alert("openEditor(data logged," + entry_name + ",1)");
							vmc.editors.start(data, entry_name, editor_type, false);
						}
					}
				});
				return;
			}
			switch(editor_type) {
				case "1" :	// VSE
				case 1	 :
					var width = "868";  // 910
					var height = "544";
					var editor_uiconf = vmc.vars.vse_uiconf;
					vmc.editors.flashvars.entry_id = entry_id;
					break;

				case "2" :	// VAE
				case 2	 :
					var width = "825";
					var height = "604";
					var editor_uiconf = vmc.vars.vae_uiconf;
					vmc.editors.params.movie = vmc.vars.service_url + "/vse/ui_conf_id/" + vmc.vars.vae_uiconf;
					vmc.editors.flashvars.entry_id = entry_id;
					break;
				default :
					alert("error: switch=default");
					break;
			}
			vmc.editors.flashvars.entry_id = entry_id;
			width = $.browser.msie ? parseInt(width) + 32 : parseInt(width) + 22;
			$("#flash_wrap").css("visibility","hidden");
			modal = vidiunInitModalBox( null, { width: width, height: height } );
			modal.innerHTML = '<div id="veditor"></div>';
			swfobject.embedSWF(	vmc.vars.service_url + "/vse/ui_conf_id/" + editor_uiconf,
								"veditor",
								width,
								height,
								"9.0.0",
								false,
								vmc.editors.flashvars,
//								vmc.utils.mergeJson(vmc.editors.flashvars, { "entry_id" : entry_id }),
								vmc.editors.params
							);
			setObjectToRemove("veditor");
		},
		flashvars: {
			"uid"			: vmc.vars.user_id, // Anonymous
			"partner_id"	: vmc.vars.partner_id,
			"subp_id"		: vmc.vars.subp_id,
			"vs"			: vmc.vars.vs,
			"vshow_id"		: "-1",
			"backF"			: "vmc.functions.closeEditor", // vse
			"saveF"			: "vmc.functions.saveEditor", // vse
			// VAE can read both formats and cases of flashvars:
			// "partnerId", "subpId", "vshowId", "entryId", "uid", "vs"
			"terms_of_use"	: vmc.vars.terms_of_use,
			"disableurlhashing" : vmc.vars.disableurlhashing,
			"jsDelegate"	: "vmc.editors.vae_functions"
		},
		params: {
			allowscriptaccess	: "always",
			allownetworking		: "all",
			bgcolor				: "#ffffff", // ? for both ?
			quality				: "high",
//			wmode				: "opaque" ,
			movie				: vmc.vars.service_url + "/vse/ui_conf_id/" + vmc.vars.vse_uiconf
		},

		vae_functions: {
			closeHandler							: function(obj) {
					vmc.utils.closeModal();
				},
			publishHandler							: vmc.functions.doNothing,
			publishFailHandler						: vmc.functions.doNothing,
			connectVoiceRecorderFailHandler			: vmc.functions.doNothing,
			getMicrophoneVoiceRecorderFailHandler	: vmc.functions.doNothing,
			initializationFailHandler				: vmc.functions.doNothing,
			initVidiunApplicationFailHandler		: vmc.functions.doNothing,
			localeFailHandler						: vmc.functions.doNothing,
			skinFailHandler							: vmc.functions.doNothing,
			getUiConfFailHandler					: vmc.functions.doNothing,
			getPluginsProviderFailHandler			: vmc.functions.doNothing,
			openVoiceRecorderHandler				: vmc.functions.doNothing,
			connectVoiceRecorderHandler				: vmc.functions.doNothing,
			startRecordingHandler					: vmc.functions.doNothing,
			recorderCancelHandler					: vmc.functions.doNothing,
			contributeVoiceRecordingHandler			: vmc.functions.doNothing,
			openContributionWizardHandler			: vmc.functions.doNothing,
			contributeEntriesHandler				: vmc.functions.doNothing,
			addTransitionHandler					: vmc.functions.doNothing,
			trimTransitionHandler					: vmc.functions.doNothing,
			addPluginHandler						: vmc.functions.doNothing,
			pluginFlagClickHandler					: vmc.functions.doNothing,
			pluginEditHandler						: vmc.functions.doNothing,
			pluginTrimHandler						: vmc.functions.doNothing,
			addAssetHandler							: vmc.functions.doNothing,
			changeSolidColorHandler					: vmc.functions.doNothing,
			trimAssetHandler						: vmc.functions.doNothing,
			duplicateHandler						: vmc.functions.doNothing,
			splitHandler							: vmc.functions.doNothing,
			reorderStoryboardHandler				: vmc.functions.doNothing,
			reorderTimelineHandler					: vmc.functions.doNothing,
			removeHandler							: vmc.functions.doNothing,
			zoomChangeHandler						: vmc.functions.doNothing,
			vidiunLogoClickHandler					: vmc.functions.doNothing,
			editVolumeLevelsButtonHandler			: vmc.functions.doNothing,
			editVolumeLevelsChangeHandler			: vmc.functions.doNothing,
			volumeOverallChangeHandler				: vmc.functions.doNothing,
			emptyTimelinesHandler					: vmc.functions.doNothing,
			showHelpHandler							: vmc.functions.doNothing,
			showVersionsWindowHandler				: vmc.functions.doNothing,
			sortMediaClipsHandler					: vmc.functions.doNothing,
			filterMediaClipsHandler					: vmc.functions.doNothing
		}
	},


// Maintain support for old vmc2 functions:

//function openCw (vs ,conversion_quality) {
//	vmc.functions.openVcw();
// }
 function expiredF() { // @todo: change all modules
	vmc.utils.expired();
 }
 function selectPlaylistContent(params) { // @todo: change call in appstudio
// function selectPlaylistContent(uiconf_id,is_playlist) {
//		alert("vmc.mediator.selectContent("+uiconf_id+","+is_playlist+")");
//		console.log(uiconf_id);
		vmc.mediator.selectContent(params.playerId,params.isPlaylist);
 }
 function logout() {
	vmc.utils.logout();
 }
 function openEditor(entry_id,entry_name,editor_type,newmix) {
	vmc.editors.start(entry_id,entry_name,editor_type,newmix);
 }
 function refreshSWF() {
//	alert("refreshSWF()");
	var state = vmc.mediator.readUrlHash();
	vmc.mediator.loadModule(state.module,state.subtab);
 }
 function openPlayer(emptystring, width, height, uiconf_id) { // for catching appstudio p&e
//	 alert("received call to openPlayer(emptystring="+emptystring+", "+"width="+width+", "+"height="+height+", uiconf_id="+uiconf_id+")");
	 vmc.preview_embed.doPreviewEmbed("multitab_playlist", null, null, true, uiconf_id); // id, name, description, is_playlist, uiconf_id
 }
// function openPlayer(id, name, description, is_playlist, uiconf_id) {
//	vmc.preview_embed.doPreviewEmbed(id, name, description, is_playlist, uiconf_id);
// }
// function openPlaylist(id, name, description, is_playlist, uiconf_id) {
//	vmc.preview_embed.doPreviewEmbed(id, name, description, is_playlist, uiconf_id);
// }
function playlistAdded() { // called from appstudio
//	alert("playlistAdded() calling vmc.preview_embed.updateList(true)");
	vmc.preview_embed.updateList(true);
}

function playerAdded() { // called from appstudio
//	alert("playerAdded() calling vmc.preview_embed.updateList(false)");
	vmc.preview_embed.updateList(false);
}

/*** end old functions ***/

//		moduleRenaming : function(module) {
//			switch(module) {
//				case "account" :
//					module = "Settings";
//				case "reports" :
//					module = "Analytics";
////				case "appstudio" :
////					module = "Studio";
//			}
//			return module;
//		},
