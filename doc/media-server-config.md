# Vidiun Server #



## Plugins: ##
- Add Wowza to plugins.ini.



## Admin Console: ##
- Add admin.ini new permissions, see admin.template.ini:
 - FEATURE_LIVE_STREAM_RECORD
 - FEATURE_VIDIUN_LIVE_STREAM
 - FEATURE_VIDIUN_LIVE_STREAM_TRANSCODE
 - FEATURE_LIVE_STREAM_ENABLE_SEGMENT_DURATION_UPDATE



## Origin Servers: ##
-  broadcast.ini according to broadcast.template.ini



## Edge Servers: ##
media_servers.ini is optional and needed only for custom configurations.

- application - defaults to vLive
- search_regex_pattern, replacement - the regular expression to be replaced in the machine name in order to get the external host name.
- domain - overwrites the machine name and the regular expression replacement with a full domain name.
- port - defaults to 1935.
- port-https - no default defined.





# Wowza #



## Prerequisites: ##
- Wowza media server 4.0.1 or above.
- Java jre 1.7.
- vidiun group (gid = 613) or any other group that apache user is associated with.
- Write access to @WEB_DIR@/content/recorded directory.
- Read access to symbolic link of @WEB_DIR@/content under @WEB_DIR@/content/recorded:
  ln –s @WEB_DIR@/content @WEB_DIR@/content/recorded/content


## Additional libraries: ##
- commons-codec-1.4.jar
- commons-httpclient-3.1.jar
- commons-logging-1.1.1.jar
- commons-lang-2.6.jar




## For all wowza machine: ##
- Copy [VidiunWowzaServer.jar](https://github.com/vidiun/media-server/releases/download/rel-3.0.8.1/VidiunWowzaServer-3.0.8.1.jar "VidiunWowzaServer.jar") to @WOWZA_DIR@/lib/
- Copy additional jar files (available in Vidiun Java client library) to @WOWZA_DIR@/lib/
 - [commons-codec-1.4.jar](https://github.com/vidiun/server-bin-linux-64bit/raw/master/wowza/commons-codec-1.4.jar "commons-codec-1.4.jar")
 - [commons-httpclient-3.1.jar](https://github.com/vidiun/server-bin-linux-64bit/raw/master/wowza/commons-httpclient-3.1.jar "commons-httpclient-3.1.jar")
 - [commons-logging-1.1.1.jar](https://github.com/vidiun/server-bin-linux-64bit/raw/master/wowza/commons-logging-1.1.1.jar "commons-logging-1.1.1.jar") 
 - [commons-lang-2.6.jar](https://github.com/vidiun/server-bin-linux-64bit/raw/master/wowza/commons-lang-2.6.jar "commons-lang-2.6.jar")
- Delete all directories under @WOWZA_DIR@/applications, but not the applications directory itself.
- Create @WOWZA_DIR@/applications/vLive directory.
- Delete all directories under @WOWZA_DIR@/conf, but not the conf directory itself.
- Create @WOWZA_DIR@/conf/vLive directory.
- Copy @WOWZA_DIR@/conf/Application.xml to @WOWZA_DIR@/conf/vLive/Application.xml

**Edit @WOWZA_DIR@/conf/vLive/Application.xml:**

 - /Root/Application/Name - vLive
 - /Root/Application/AppType - Live
 - /Root/Application/Streams/StreamType - live
 - /Root/Application/Streams/StorageDir - @WEB_DIR@/content/recorded
 - /Root/Application/Streams/LiveStreamPacketizers: 
	 - cupertinostreamingpacketizer
	 - mpegdashstreamingpacketizer
	 - sanjosestreamingpacketizer
	 - smoothstreamingpacketizer
	 - dvrstreamingpacketizer
 - /Root/Application/Streams/Properties:
```xml
<Property>
	<Name>sortPackets</Name>
	<Value>true</Value>
	<Type>Boolean</Type>
</Property>
<Property>
	<Name>sortBufferSize</Name>
	<Value>6000</Value>
	<Type>Integer</Type>
</Property>
```

 - /Root/Application/Transcoder/LiveStreamTranscoder - transcoder
 - /Root/Application/Transcoder/Templates - `http://@WWW_HOST@/api_v3/index.php/service/wowza_liveConversionProfile/action/serve/entryId/${SourceStreamName}/f/transcode.xml`
 - /Root/Application/Transcoder/Properties:
```xml
<Property>
	<Name>sortPackets</Name>
	<Value>true</Value>
	<Type>Boolean</Type>
</Property>
<Property>
	<Name>sortBufferSize</Name>
	<Value>4000</Value>
	<Type>Integer</Type>
</Property>
```

 - /Root/Application/DVR/Recorders - dvrrecorder
 - /Root/Application/DVR/Store - dvrfilestorage
 - /Root/Application/DVR/Properties:
```xml
<Property>
	<Name>httpRandomizeMediaName</Name>
	<Value>true</Value>
	<Type>Boolean</Type>
</Property>
<Property>
	<Name>dvrAudioOnlyChunkTargetDuration</Name>
	<Value>10000</Value>
	<Type>Integer</Type>
</Property>
<Property>
	<Name>dvrChunkDurationMinimum</Name>
	<Value>1000</Value>
	<Type>Integer</Type>
</Property>
<Property>
	<Name>dvrMinimumAvailableChunks</Name>
	<Value>5</Value>
	<Type>Integer</Type>
</Property>
```

 - /Root/Application/HTTPStreamers:
	 - cupertinostreaming
	 - smoothstreaming
	 - sanjosestreaming
	 - mpegdashstreaming
	 - dvrchunkstreaming
 - /Root/Application/LiveStreamPacketizer/Properties:
```xml
<Property>
	<Name>httpRandomizeMediaName</Name>
	<Value>true</Value>
	<Type>Boolean</Type>
</Property>
<Property>
	<Name>cupertinoPlaylistChunkCount</Name>
	<Value>10</Value>
	<Type>Integer</Type>
</Property>
<Property>
	<Name>cupertinoRepeaterChunkCount</Name>
	<Value>10</Value>
	<Type>Integer</Type>
</Property>
<Property>
	<Name>sanjoseChunkDurationTarget</Name>
	<Value>10000</Value>
	<Type>Integer</Type>
</Property>
<Property>
	<Name>sanjoseMaxChunkCount</Name>
	<Value>10</Value>
	<Type>Integer</Type>
</Property>
<Property>
	<Name>sanjosePlaylistChunkCount</Name>
	<Value>4</Value>
	<Type>Integer</Type>
</Property>
<Property>
	<Name>sanjoseRepeaterChunkCount</Name>
	<Value>4</Value>
	<Type>Integer</Type>
</Property>
```

 - /Root/Application/HTTPStreamer/Properties:
```xml
<Property>
	<Name>httpOriginMode</Name>
	<Value>on</Value>
</Property>
<Property>
	<Name>cupertinoCacheControlPlaylist</Name>
	<Value>max-age=3</Value>
</Property>
<Property>
	<Name>cupertinoCacheControlMediaChunk</Name>
	<Value>max-age=86400</Value>
</Property>
<Property>
	<Name>cupertinoOnChunkStartResetCounter</Name>
	<Value>true</Value>
	<Type>Boolean</Type>
</Property>
<Property>
	<Name>smoothCacheControlPlaylist</Name>
	<Value>max-age=3</Value>
</Property>
<Property>
	<Name>smoothCacheControlMediaChunk</Name>
	<Value>max-age=86400</Value>
</Property>
<Property>
	<Name>smoothCacheControlDataChunk</Name>
	<Value>max-age=86400</Value>
</Property>
<Property>
	<Name>sanjoseCacheControlPlaylist</Name>
	<Value>max-age=3</Value>
</Property>
<Property>
	<Name>sanjoseCacheControlMediaChunk</Name>
	<Value>max-age=86400</Value>
</Property>
```

 - /Root/Application/Modules, add:
```xml
<Module>
	<Name>LiveStreamEntry</Name>
	<Description>LiveStreamEntry</Description>
	<Class>com.vidiun.media.server.wowza.listeners.LiveStreamEntry</Class>
</Module>
```
 
 - /Root/Application/Properties, add new Property:
```xml
<Property>
	<Name>streamTimeout</Name>
	<Value>200</Value>
	<Type>Integer</Type>
</Property>
<Property>
	<Name>securityPublishRequirePassword</Name>
	<Value>false</Value>
	<Type>Boolean</Type>
</Property>
```



**Edit @WOWZA_DIR@/conf/Server.xml:**

 - /Root/Server/ServerListeners:
```xml
<ServerListener>
	<BaseClass>com.vidiun.media.server.wowza.listeners.ServerListener</BaseClass>
</ServerListener>
```

 - /Root/Server/Properties:
```xml
<Property>
	<Name>VidiunServerURL</Name>
	<Value>http://@WWW_DIR@</Value>
</Property>
<Property>
	<!-- Vidiun media server partner (-5) admin secret -->
	<Name>VidiunServerAdminSecret</Name>
	<Value>@MEDIA_PARTNER_ADMIN_SECRET@</Value>
</Property>
<Property>
	<!-- Vidiun API http timeout -->
	<Name>VidiunServerTimeout</Name>
	<Value>30</Value>
</Property>
<Property>
	<!-- Vidiun server managers to be loaded -->
	<Name>VidiunServerManagers</Name>
	<Value>com.vidiun.media.server.wowza.StatusManager, com.vidiun.media.server.wowza.LiveStreamManager</Value>
</Property>
<Property>
	<!-- Vidiun web services to be loaded -->
	<Name>VidiunServerWebServices</Name>
	<Value>com.vidiun.media.server.api.services.VidiunLiveService</Value>
</Property>
<Property>
	<!-- Vidiun server status reporting interval, in seconds -->
	<Name>VidiunServerStatusInterval</Name>
	<Value>300</Value>
</Property>
<Property>
	<!-- Vidiun interval to update that live stream entry is still broadcasting, in seconds -->
	<Name>VidiunLiveStreamKeepAliveInterval</Name>
	<Value>60</Value>
</Property>
<Property>
	<!-- Vidiun maximum DVR window, in seconds, should be 24 hours -->
	<Name>VidiunLiveStreamMaxDvrWindow</Name>
	<Value>7200</Value>
</Property>
<Property>
	<!-- Vidiun maximum recorded chunk duration, in minutes, should be an hour -->
	<Name>VidiunRecordedChunckMaxDuration</Name>
	<Value>60</Value>
</Property>
<Property>
	<!-- Vidiun web services http port -->
	<Name>VidiunServerWebServicesPort</Name>
	<Value>888</Value>
</Property>
<Property>
	<!-- Vidiun web services binding host name -->
	<Name>VidiunServerWebServicesHost</Name>
	<Value>0.0.0.0</Value>
</Property>
<Property>
	<!-- Vidiun recorded file group -->
	<Name>VidiunRecordedFileGroup</Name>
	<!-- vidiun (gid = 613) or any other group that apache user is associated with. -->
	<Value>vidiun</Value>
</Property>
<Property>
	<!-- Minimum buffering time before registering entry as is-live (in seconds) -->
	<Name>VidiunIsLiveRegistrationMinBufferTime</Name>
	<Value>60</Value>
</Property>
```


**Edit @WOWZA_DIR@/conf/log4j.properties:**

 - Add `log4j.logger.com.vidiun` = `DEBUG`
 - Comment out `log4j.appender.serverAccess.layout` and its sub values `log4j.appender.serverAccess.layout.*` 
 - Add `log4j.appender.serverAccess.layout` = `org.apache.log4j.PatternLayout`
 - Add `log4j.appender.serverAccess.layout.ConversionPattern` = `[%d{yyyy-MM-dd HH:mm:ss}][%t][%C:%M] %p - %m - (%F:%L) %n`
 - Change `log4j.appender.serverAccess.File` = `@LOG_DIR@/vidiun_mediaserver_access.log`
 - Comment out `log4j.appender.serverError.layout` and its sub values `log4j.appender.serverError.layout.*` 
 - Add `log4j.appender.serverError.layout` = `org.apache.log4j.PatternLayout`
 - Add `log4j.appender.serverError.layout.ConversionPattern` = `[%d{yyyy-MM-dd HH:mm:ss}][%t][%C:%M] %p - %m - (%F:%L) %n` 
 - Change `log4j.appender.serverError.File` = `@LOG_DIR@/vidiun_mediaserver_error.log`
 - Change `log4j.appender.serverStats.File` = `@LOG_DIR@/vidiun_mediaserver_stats.log`



**Setting keystore.jks:**

- [Create a self-signed SSL certificate](http://www.wowza.com/forums/content.php?435 "Create a self-signed SSL certificate") or use existing one.
- Copy the certificate file to @WOWZA_DIR@/conf/keystore.jks


**Edit @WOWZA_DIR@/conf/VHost.xml:**

- Uncomment /Root/VHost/HostPortList/HostPort with port 443 for SSL.
- /Root/VHost/HostPortList/HostPort/SSLConfig/KeyStorePassword - set the password for your certificate file.



## For webcam recording servers: ##

**Create oflaDemo application**

 - Create oflaDemo application in your Wowza server.
  - Create @WOWZA_DIR@/applications/oflaDemo directory
  - Create @WOWZA_DIR@/conf/oflaDemo directory
  - Copy @WOWZA_DIR@/conf/Application.xml to @WOWZA_DIR@/conf/oflaDemo/Application.xml.
 - Configure @WOWZA_DIR@/conf/oflaDemo/Application.xml
  - /Root/Streams/StreamType - live-record
  - /Root/Streams/StorageDir - @WEB_DIR@/content/webcam
  - /Root/Transcoder/LiveStreamTranscoder - transcoder
  - /Root/Transcoder/Templates - hdfvr.xml


**Create transcoding template**

 - Create @WOWZA_DIR@/transcoder/templates/hdfvr.xml template:

```xml
<Root>
	<Transcode>
		<Encodes>
			<!-- Example Encode block for source, not required unless Member of StreamNameGroup. -->
			<Encode>
				<Enable>true</Enable>
				<Name>aac</Name>
				<StreamName>mp4:${SourceStreamName}</StreamName>
				<Video>
					<!-- H.264, PassThru, Disable -->
					<Codec>PassThru</Codec>
					<Bitrate>${SourceVideoBitrate}</Bitrate>
					<Parameters>
					</Parameters>
				</Video>
				<Audio>
					<!-- AAC, PassThru, Disable -->
					<Codec>AAC</Codec>
					<Bitrate>48000</Bitrate>
				</Audio>
				<Properties>
				</Properties>
			</Encode>
		</Encodes>
		<Decode>
		</Decode>
		<StreamNameGroups>
		</StreamNameGroups>
		<Properties>
		</Properties>
	</Transcode>
</Root>
```

**Configure file system**

 - Make sure that @WEB_DIR@/content/webcam group is vidiun or apache
 - Define permissions stickiness on the group:
  - chmod +t @WEB_DIR@/content/webcam
  - chmod g+s @WEB_DIR@/content/webcam
