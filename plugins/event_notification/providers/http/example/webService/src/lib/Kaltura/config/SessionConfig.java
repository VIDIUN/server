package lib.Vidiun.config;


import com.vidiun.client.VidiunClient;
import com.vidiun.client.VidiunConfiguration;
import com.vidiun.client.enums.VidiunSessionType;

/**
 * This class centralizes the session configuration 
 */
public class SessionConfig {
	
	/** The partner who is executing this client */
	public static final int VIDIUN_PARTNER_ID = PARTNER_ID;
	/** The secret of the indicated partner */
	public static final String VIDIUN_ADMIN_SECRET = "VIDIUN_ADMIN_SECRET";
	/** Vidiun service url - the end point*/
	public static final String VIDIUN_SERVICE_URL = "END-POINT";
	
	/**
	 * This function generates Vidiun Client according to the given ids
	 * @param sessionType VidiunSessionType - whether the session is admin or user session
	 * @param userId String - The user ID.
	 * @param sessionExpiry int - The session expire value. 
	 * @param sessionPrivileges String - The session privileges. 
	 * @return The generated client
	 * @throws Exception In case the client generation failed for some reason.
	 */
	public static VidiunClient getClient(VidiunSessionType sessionType, String userId, int sessionExpiry, String sessionPrivileges) throws Exception {
		
		// Create VidiunClient object using the accound configuration
		VidiunConfiguration config = new VidiunConfiguration();
		config.setPartnerId(VIDIUN_PARTNER_ID);
		config.setEndpoint(VIDIUN_SERVICE_URL);
		VidiunClient client = new VidiunClient(config);
		
		// Generate VS string locally, without calling the API
		String vs = client.generateSession(
			VIDIUN_ADMIN_SECRET,
			userId,
			sessionType,
			config.getPartnerId(),
			sessionExpiry,
			sessionPrivileges
		);
		client.setSessionId(vs);
		
		// Returns the VidiunClient object
		return client;
	}
}