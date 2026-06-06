export interface AccountRequest {
    id: number;
    full_name: string;
    email: string;
    identity_number: string;
    deposit_amount: number;
    status: "pending" | "accepted" | "rejected";
}
export interface AdminTableProps {
    requests: AccountRequest[];
    onAccept: (id: number) => void;
    onReject: (id: number) => void;
}