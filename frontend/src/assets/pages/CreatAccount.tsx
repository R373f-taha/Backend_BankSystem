import { useState } from "react"; 
import Form from '../components/sharedComponents/formShared/Form';
import type { Field } from '../components/sharedComponents/interfaces/form';
import { useNavigate } from "react-router-dom"; 

export default function CreatAccount() {
    const navigate = useNavigate(); 

    const [popup, setPopup] = useState<{ show: boolean; type: "success" | "error"; message: string }>({
        show: false,
        type: "success",
        message: ""
    });
    //form field
    const signUpFields: Field[] = [
        { name: "full_name", type: "text", placeholder: "Enter your Full Name" }, 
        { name: "email", type: "email", placeholder: "Enter your Email" },
        { name: "password", type: "password", placeholder: "Create a Password" }, 
        { 
            name: "gender", 
            type: "select", 
            placeholder: "Select Gender",
            options: [{ label: "Male", value: "male" }, { label: "Female", value: "female" }]
        },
        { name: "address", type: "text", placeholder: "Enter your Address" },
        { 
            name: "marital_status", 
            type: "select", 
            placeholder: "Select Marital Status",
            options: [{ label: "Single", value: "single" }, { label: "Married", value: "married" }]
        },
        { name: "identity_number", type: "text", placeholder: "Enter Identity Number" },
        { name: "date_of_birth", type: "date", placeholder: "Date of Birth" },
        { name: "occupation", type: "text", placeholder: "Enter your Occupation" },
        { name: "deposit_amount", type: "number", placeholder: "Enter Deposit Amount" },
    ];

    const handleSignUpSubmit = async (data: Record<string, string>) => {
        // create formData
        const formData = new FormData();
        
        Object.keys(data).forEach((key) => {
            if (key === 'date_of_birth' && data[key]) {
                // transform date from arabic to english
                const cleanedDate = data[key].replace(/[٠-٩]/g, (d) => String.fromCharCode(d.charCodeAt(0) - 1632))
                                                .replace(/[۰-۹]/g, (d) => String.fromCharCode(d.charCodeAt(0) - 1776));
                formData.append(key, cleanedDate);
            } else {
                formData.append(key, data[key]);
            }
        });

        try {
            // send data
            const response = await fetch("http://127.0.0.1:8000/api/account/account-request", {
                method: "POST",
                body: formData, 
                cache: "no-store"
            });

            const result = await response.json();

            if (response.ok) {
                setPopup({ 
                    show: true, 
                    type: "success", 
                    message: result.data.message 
                });
                
                setTimeout(() => {
                    setPopup(prev => ({ ...prev, show: false }));
                    navigate("/verify-account", { state: { email: data.email } });
                }, 2500);
            } else {
                // check errors
                let customErrorMessage = result.message || "Failed to submit request.";
                if (result.errors) {
                    const firstKey = Object.keys(result.errors)[0];
                    customErrorMessage = result.errors[firstKey][0]; 
                }
                setPopup({ show: true, type: "error", message: customErrorMessage });
            }
        } catch (error) {
            console.log(error)
            setPopup({ show: true, type: "error", message: "Network error. Please check your backend server." });
        }
    };

    return (
        <>
            <Form
                title="Submit Request"
                description="Please fill in your authentic details below to submit an official bank account opening request."
                submitText="Submit Request"
                secondaryText="Login"
                fields={signUpFields} 
                onSubmit={handleSignUpSubmit} 
            />
            
            {popup.show && (
                <div className="mh-popup-overlay">
                    <div className={`mh-popup-card ${popup.type}`}>
                        <h3>{popup.type === "success" ? "Submitted!" : "Oops!"}</h3>
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