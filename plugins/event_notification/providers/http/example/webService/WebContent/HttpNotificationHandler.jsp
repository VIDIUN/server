<%@page import="java.io.BufferedReader"%>
<%@page import="org.w3c.dom.Element"%>
<%@page import="com.vidiun.client.utils.XmlUtils"%>
<%@page import="lib.Vidiun.HttpNotificationHandler"%>
<%@page import="com.vidiun.client.types.VidiunHttpNotification"%>
<%@page import="com.vidiun.client.utils.ParseUtils"%>
<%@page import="lib.Vidiun.RequestHandler"%>
<%

BufferedReader reader = request.getReader();
StringBuffer sb = new StringBuffer("");
String line;
while ((line = reader.readLine()) != null){
	sb.append(new String(line.getBytes("ISO-8859-1"), "UTF-8"));
}
reader.reset();

String xml = sb.toString();
String signature = request.getHeader("x-vidiun-signature");
RequestHandler.validateSignature(xml, SessionConfig.VIDIUN_ADMIN_SECRET, signature);

int dataOffset = xml.indexOf("data=");
if(dataOffset < 0) {
	System.out.println("Couldn't find data");
}

String xmlData = xml.substring(5);
Element xmlElement = XmlUtils.parseXml(xmlData);
VidiunHttpNotification httpNotification = ParseUtils.parseObject(VidiunHttpNotification.class, xmlElement);

HttpNotificationHandler handler = new HttpNotificationHandler();
handler.handle(httpNotification);
handler.finalize();

%>