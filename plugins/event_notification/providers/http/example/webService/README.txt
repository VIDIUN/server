
1. Update session config:
	at NewNotificationHandler/src/lib/Vidiun/config/SessionConfig.java
	Please update the partner_id, admin_secret & service_url

2. Update Sync sample handler:
	at NewNotificationHandler/src/lib/Vidiun/notification/handlers/SyncSampleHandler.java
	Please update metadata profile id, approval field and sync field name

3. Add the required jars
	under 'NewNotificationHandler\WebContent\WEB-INF\lib' add all the required jars.
	- VidiunClientLibrary
	- commons-httpclient-3.1
	- commons-codec-1.4
	- commons-logging-1.1.1
	- log4j-1.2.15
	
4 & optional. Update the handler to handle the notification.
	at NewNotificationHandler/src/lib/Vidiun/notification/handlers/SyncSampleHandler.java
	update both 'deleteReference' and 'syncReference'
	add more cases to handle if you'd like
	
5. Deploy on tomcat:
	- Extract a WAR file and put it under <TOMCAT>/webapps

6. Contact your project manager to set the notification URL to be http://<your tomcat server path>/<war name>/HttpNotificationHandler.jsp
	