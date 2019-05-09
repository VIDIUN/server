<%@ page import = "java.util.Map.Entry" %>
<%@ page import = "java.util.HashMap" %>
<%@ page import = "org.w3c.dom.Element" %>
<%@ page import = "com.vidiun.client.utils.ParseUtils" %>
<%@ page import = "com.vidiun.client.utils.XmlUtils" %>
<%@ page import = "com.vidiun.client.types.VidiunHttpNotification" %>
<%
String xmlData = request.getParameter("data");
Element xmlElement = XmlUtils.parseXml(xmlData);
VidiunHttpNotification httpNotification = ParseUtils.parseObject(VidiunHttpNotification.class, xmlElement);
HashMap<String, String> params = httpNotification.toParams();
for (Entry<String, String> itr : params.entrySet()) {
	out.println(itr.getKey() + " => " + itr.getValue());
}
%>