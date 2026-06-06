// This file defines the structure of a Notification object used in the application.
export interface Notification {
    id: number;
    message: string;
    read_at: string | null;
    created_at: string;
}