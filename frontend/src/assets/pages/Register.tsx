import { useState } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import Form from "../components/sharedComponents/formShared/Form";
import type { Field } from "../components/sharedComponents/interfaces/form";

export default function Register() {
    const navigate = useNavigate();
    const location = useLocation();
    const incomingEmail = location.state?.email || "";

    const [popup, setPopup] = useState<{ show: boolean; type: "success" | "error"; message: string }>({
        show: false,
        type: "success",
        message: ""
    });

    const registerFields: Field[] = [
        { name: "name", type: "text", placeholder: "Enter your Full Name" },
        { name: "email", type: "email", placeholder: "Enter your Email" },
        { name: "password", type: "password", placeholder: "Create Password" },
        { name: "password_confirmation", type: "password", placeholder: "Confirm Password" },
    ];

    const handleRegisterSubmit = async (data: Record<string, string>) => {
        const formData = new FormData();
        formData.append("name", data.name);
        formData.append("email", data.email || incomingEmail);
        formData.append("password", data.password);
        formData.append("password_confirmation", data.password_confirmation);

        try {
            const response = await fetch("http://127.0.0.1:8000/api/register", {
                method: "POST",
                body: formData,
            });

            const result = await response.json();

            if (response.ok) {
                setPopup({ show: true, type: "success", message: "Account created! Redirecting to login..." });
                setTimeout(() => navigate("/login"), 2500);
            } else {
                let errorMessage = result.message || "Registration failed.";
                if (result.errors) {
                    const firstKey = Object.keys(result.errors)[0];
                    errorMessage = result.errors[firstKey][0];
                }
                setPopup({ show: true, type: "error", message: errorMessage });
            }
        } catch (error) {
            console.log(error)
            setPopup({ show: true, type: "error", message: "Network error. Please check your backend server." });
        }
    };

    return (
        <>
            <Form
                title="Create Account"
                description="Set your password to complete your account registration."
                fields={registerFields}
                submitText="Create Account"
                secondaryText="Login"
                onSubmit={handleRegisterSubmit}
            />

            {popup.show && (
                <div className="mh-popup-overlay">
                    <div className={`mh-popup-card ${popup.type}`}>
                        <h3>{popup.type === "success" ? "Success!" : "Oops!"}</h3>
                        <p>{popup.message}</p>
                        {popup.type === "error" && (
                            <button className="mh-popup-close-btn" onClick={() => setPopup(prev => ({ ...prev, show: false }))}>
                                Close
                            </button>
                        )}
                    </div>
                </div>
            )}
        </>
    );
}