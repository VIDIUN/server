<h2>Overview</h2>
<p>
	Vidiun XML Schema Definitions (XSDs) are standard W3C XSD version 1.0 formatted documents that define the structure of Vidiun�s XML-formatted integration interfaces.
</p>
<p>
	Publishers and developers may use the schemas when applying or developing XML-based content management-related integrations with the Vidiun Platform. The Vidiun XML-based interfaces related to content management include bulk content ingestion, cue points handling, and content syndication.
</p>

Each XML schema: 
<ul>
	<li>Defines XML elements and attributes</li> 
	<li>Defines whether the element or attribute is required or optional, and the maximum number of appearances.</li> 
	<li>Defines the content type of the XML element or attribute</li> 
	<li>Defines the sequence order of the XML element or attribute within the XML</li>
	<li>Includes descriptions and examples of XML elements and attributes</li> 
</ul>

The following XML types are supported:
<ol>
	<li>
		XMLs that are generated by the Vidiun server and as such are fully compliant with their schema definition.<br/> 
		<b>Examples:</b> Syndication Feed XMLs, Bulk Upload Results XMLs, Cue Point Serve XMLs
	</li>
	<li>
		XMLs that are submitted to the Vidiun server<br/>
		<b>Examples:</b> Bulk Upload XMLs, Cue Point Ingest XMLs, Drop Folder XMLs<br/>
		Each XML document and file submitted to the Vidiun server is validated against its schema to ensure that a correct server action is committed. The following levels of validation apply:
		<ol type="a">
			<li>XML structure validation (including inspection of XML illegal characters such as: &amp;, &lt;, &gt;, �, �, (, )</li>
			<li>Compliance of the XML with its schema, including element appearance, structure, and order</li>
			<li>Application level validation, including compliance of XML values with system and account-specific settings</li>
		</ol>
	</li>
</ol>