import type { Notification } from "../../sharedComponents/interfaces/Notification";
import './DashboardNotifications.css'
interface Props {
    notifications: Notification[];
    unreadCount: number;
    loading: boolean;
    onMarkAsRead: (id: number) => void;
    onMarkAllAsRead: () => void;
    onDelete: (id: number) => void;
}

export const DashboardNotification = ({ 
    notifications, unreadCount, loading, onMarkAsRead, onMarkAllAsRead, onDelete 
}: Props) => (
    <section className="notifications-section">
        <div className="notifications-header">
            <h2>🔔 Notifications {unreadCount > 0 && <span className="unread-badge">{unreadCount}</span>}</h2>
            {notifications.length > 0 && (
                <button onClick={onMarkAllAsRead} className="mark-all-btn">Mark All as Read</button>
            )}
        </div>

        {loading ? (
            <p>Loading...</p>
        ) : notifications.length === 0 ? (
            <p className="no-notifications">No notifications available 😊</p>
        ) : (
            <ul className="notifications-list">
                {notifications.map((notif) => (
                    <li key={notif.id} className={`notification-item ${notif.read_at ? "read" : "unread"}`}>
                        <p>{notif.message}</p>
                        <span className="notif-date">{new Date(notif.created_at).toLocaleDateString()}</span>
                        <div className="notif-actions">
                            {!notif.read_at && (
                                <button onClick={() => onMarkAsRead(notif.id)}>Mark as Read</button>
                            )}
                            <button onClick={() => onDelete(notif.id)} className="delete-btn">Delete</button>
                        </div>
                    </li>
                ))}
            </ul>
        )}
    </section>
);