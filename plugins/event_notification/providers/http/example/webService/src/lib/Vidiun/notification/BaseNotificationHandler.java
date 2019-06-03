package lib.Vidiun.notification;

import lib.Vidiun.config.SessionConfig;
import lib.Vidiun.output.Console;

import com.vidiun.client.VidiunApiException;
import com.vidiun.client.VidiunClient;
import com.vidiun.client.enums.VidiunSessionType;
import com.vidiun.client.types.VidiunHttpNotification;

/**
 * This class is a base class for all the notification handlers 
 */
public abstract class BaseNotificationHandler {

	/** Vidiun client */
	private static VidiunClient apiClient = null;
	
	/** The console this handler use*/
	protected Console console;
	
	/**
	 * Constructor
	 * @param console
	 */
	public BaseNotificationHandler(Console console) {
		this.console = console;
	}

	/**
	 * @return The Vidiun client
	 * @throws Exception
	 */
	protected static VidiunClient getClient() {
		if (apiClient == null) {
			// Generates the Vidiun client. The parameters can be changed according to the need
			try {
				apiClient = SessionConfig.getClient(VidiunSessionType.ADMIN, "", 86400, "");
			} catch (Exception e) {
				throw new NotificationHandlerException("Failed to generate client : " + e.getMessage(), NotificationHandlerException.ERROR_PROCESSING);
			}
		}
		return apiClient;
	}

	/**
	 * This function decides whether this handle should handle the notification
	 * @param httpNotification The notification that is considered to be handled
	 * @return Whether this handler should handle this notification
	 */
	abstract public boolean shouldHandle(VidiunHttpNotification httpNotification);

	/**
	 * The handling function. 
	 * @param httpNotification The notification that should be handled
	 * @throws VidiunApiException In case something bad happened
	 */
	abstract public void handle(VidiunHttpNotification httpNotification);

	/**
	 * @return The notification processing timing
	 */
	public HandlerProcessType getType() {
		return HandlerProcessType.PROCESS;
	}
}