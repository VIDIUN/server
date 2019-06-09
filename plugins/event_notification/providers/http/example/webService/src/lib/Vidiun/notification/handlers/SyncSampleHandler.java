package lib.Vidiun.notification.handlers;

import lib.Vidiun.notification.BaseNotificationHandler;
import lib.Vidiun.notification.NotificationHandlerException;
import lib.Vidiun.output.Console;

import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

import com.vidiun.client.VidiunApiException;
import com.vidiun.client.enums.VidiunEventNotificationEventObjectType;
import com.vidiun.client.enums.VidiunEventNotificationEventType;
import com.vidiun.client.enums.VidiunMetadataObjectType;
import com.vidiun.client.types.VidiunHttpNotification;
import com.vidiun.client.types.VidiunMediaEntry;
import com.vidiun.client.types.VidiunMetadata;
import com.vidiun.client.types.VidiunMetadataFilter;
import com.vidiun.client.types.VidiunMetadataListResponse;
import com.vidiun.client.utils.XmlUtils;

public class SyncSampleHandler extends BaseNotificationHandler {

	// Handler constants
	protected static final int METADATA_PROFILE_ID = METADATA_PROFILE_ID;
	protected static final String APPROVAL_FIELD_NAME = "ApprovalStatus";
	protected static final String SYNC_FIELD_NAME = "SyncStatus";
	
	// Constants
	protected static final String SYNC_NEEDED = "Sync Needed";
	protected static final String SYNC_DONE = "Sync Done";
	
	public SyncSampleHandler(Console console) {
		super(console);
	}
	
	public boolean shouldHandle(VidiunHttpNotification httpNotification) {
		// Only handles if the event type is custom-metadata field changed.
		if(!((httpNotification.eventType.equals(VidiunEventNotificationEventType.OBJECT_DATA_CHANGED)) &&
				(httpNotification.eventObjectType.equals(VidiunEventNotificationEventObjectType.METADATA)))) 
			return false;
		
		// Only handle metadata of entries
		VidiunMetadata object = (VidiunMetadata)httpNotification.object;
		
		// Test that the metadata profile is the one we test
		if(object.metadataProfileId != METADATA_PROFILE_ID)
			return false;
		
		return (object.metadataObjectType == VidiunMetadataObjectType.ENTRY);
	}
	
	/**
	 * The handling function. 
	 * @param httpNotification The notification that should be handled
	 * @throws VidiunApiException In case something bad happened
	 */
	public void handle(VidiunHttpNotification httpNotification) {
	
		try {
			// Since the custom-metadata is the returned object, there is no need in querying it.
			VidiunMetadata metadata = (VidiunMetadata)httpNotification.object;
			
			// If the custom metadata in within another custom-metadata profile, retrieve it by executing
			// VidiunMetadata extraMetadata = fetchMetadata(metadata.objectId, OTHER METADATA PROFILE ID)
			
			String approvalStatus = getValue(metadata.xml, APPROVAL_FIELD_NAME);
			if(approvalStatus == null)
				return;
			
			// Entry retrieval for basic and common attributes.
			VidiunMediaEntry entry = fetchEntry(metadata.objectId);
			
			if(approvalStatus.equals("Ready For Website")) {
				handleReadyForSite(entry, metadata);
			} else if (approvalStatus.equals("Deleted")) {
				handleDelete(entry, metadata);
			} 
			// TODO - Add other cases here, in this code sample we're only monitoring these values. 
			
		} catch (VidiunApiException e) {
			console.write("Failed while handling notification");
			console.write(e.getMessage());
			throw new NotificationHandlerException("Failed while handling notification" + e.getMessage(), NotificationHandlerException.ERROR_PROCESSING);
		}
	}
	
	/**
     * Fetch an entry using the API
     *
     * @param String, entryId: id of the entry you want to fetch
     * @return 
     * @throws VidiunApiException 
     * @throws Exception 
     *
     */
	protected VidiunMediaEntry fetchEntry(String entryId) throws VidiunApiException{
		 return getClient().getMediaService().get(entryId);
	}
	
	/**
	 * This function fetches the metadata of a given type for a given entry
	 * @param entryId The entry for which we fetch the metadata
	 * @param metadataProfileId The metadata profile id
	 * @return The matching metadata
	 * @throws VidiunApiException
	 */
	protected VidiunMetadata fetchMetadata(String entryId, int metadataProfileId)
			throws VidiunApiException {
		VidiunMetadataFilter filter = new VidiunMetadataFilter();
		filter.objectIdEqual = entryId;
		filter.metadataProfileIdEqual = metadataProfileId;
		VidiunMetadataListResponse metadatas = getClient().getMetadataService().list(filter);
		if(metadatas.totalCount == 0) {
			console.write("Failed to retrieve metadata for entry " + entryId + " and profile " + metadataProfileId);
			return null;
		}
		
		VidiunMetadata metadata = metadatas.objects.get(0);
		console.write("Successfully retrieved metadata. ID " + metadata.id);
		return metadata;
	}
	
	/**
	 * This function handles the case in which an entry was marked as deleted
	 * @param entry The entry.
	 * @param syncMetadata The SyncMetadataObject
	 * @throws VidiunApiException
	 */
	protected void handleDelete(VidiunMediaEntry entry, VidiunMetadata syncMetadata) throws VidiunApiException {
		if(!SYNC_DONE.equals(getValue(syncMetadata.xml, SYNC_FIELD_NAME))) {
			console.write("Entry is not marked as synched with the CMS, do nothing");
			return;
		}
		
		console.write("The entry " + entry.name + " has been marked as deleted on Vidiun. Sync this delete with customer's website CMS");
		deleteReference(entry, syncMetadata);
		// Mark the entry again as sync needed as we removed it from the CMS
		updateSyncStatus(syncMetadata, SYNC_NEEDED);
	}

	/**
	 * This function handles the case in which an entry is ready for site
	 * @param entry The entry.
	 * @param syncMetadata The SyncMetadataObject
	 * @throws VidiunApiException
	 */
	protected void handleReadyForSite(VidiunMediaEntry entry, VidiunMetadata syncMetadata) throws VidiunApiException {
		if(!SYNC_NEEDED.equals(getValue(syncMetadata.xml, SYNC_FIELD_NAME))) {
			console.write("No sync is needed");
			return;
		}
		
		console.write("The entry " + entry.name + " has been approved to be synced with customer's website CMS");
		syncReference(entry, syncMetadata);
		updateSyncStatus(syncMetadata, SYNC_DONE);
	}

	/**
	 * This function updates the sync field value
	 * @param object The metadata object we'd like to update
	 * @param newValue The new value for the sync field
	 * @throws VidiunApiException
	 */
	protected void updateSyncStatus(VidiunMetadata object, String newValue) throws VidiunApiException {
		String xml = object.xml;
		String oldValue = getValue(xml, SYNC_FIELD_NAME);
		String oldStr = "<" + SYNC_FIELD_NAME +">" + oldValue + "</" + SYNC_FIELD_NAME +">";
		String newStr = "<" + SYNC_FIELD_NAME +">" + newValue + "</" + SYNC_FIELD_NAME +">";
		xml = xml.replaceAll(oldStr, newStr);
		
		getClient().getMetadataService().update(object.id, xml);
	}

	/**
	 * This function parses an XML and returns a specific field value from it
	 * @param xml The parsed XML
	 * @param fieldName The field name we want to retrieve
	 * @return The field avtual value
	 * @throws VidiunApiException
	 */
	protected static String getValue(String xml, String fieldName) throws VidiunApiException {
		Element xmlElement = XmlUtils.parseXml(xml);
		NodeList childNodes = xmlElement.getChildNodes();
		for (int i = 0; i < childNodes.getLength(); i++) {
			Node aNode = childNodes.item(i);
			String nodeName = aNode.getNodeName();
			if (nodeName.equals(fieldName))
				return aNode.getTextContent();
		}
		return null;
	}
	
	// Customer specific functions
	
	/**
	 * This function should delete an object reference from the external system 
	 * @param entry The entry that has to be deleted
	 * @param object The metadata object describing the object
	 */
	protected void deleteReference(VidiunMediaEntry entry, VidiunMetadata object) {
		console.write("Delete this entry's reference from your external system");
		// TODO - Add your code here
	}
	
	/**
	 * This function should sync an object reference to the external system 
	 * @param entry The entry that has to be synced
	 * @param object The metadata object describing the object
	 */
	protected void syncReference(VidiunMediaEntry entry, VidiunMetadata object) {
		console.write("Sync the entry to your external system");
		// TODO - Add your code here
	}
}
