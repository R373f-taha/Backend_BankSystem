import { Outlet } from "react-router-dom";
import NavBar from "../navBar/NavBar";
import type { NavItem } from "../interfaces/NavBar";
import logoImg from '../../../images/homeImages/Logo.png'; 
import menuIcon from '../../../images/homeImages/Mobile Icon.png';
import Footer from "../footer/Footer";
import mailIcon from "../../../images/homeImages/Email.png";
import phoneIcon from "../../../images/homeImages/Phone.png";
import locationIcon from "../../../images/homeImages/Location.png";
import facebookIcon from "../../../images/homeImages/Facebook.png";
import twitterIcon from "../../../images/homeImages/Twitter.png";
import linkedInIcon from "../../../images/homeImages/LinkedIn.png";
import { AuthProvider } from "../../../context/AuthContext"; 

const contactData = [
    { icon: mailIcon, content: "hello@skillbridge.com" },
    { icon: phoneIcon, content: "+91 91813 23 2309" },
    { icon: locationIcon, content: "Somewhere in the World" }
];

const socialIcons = [
    { content: facebookIcon, href: "https://facebook.com" },
    { content: twitterIcon, href: "https://x.com" },
    { content: linkedInIcon, href: "https://linkedin.com" }
];

const RootLayout = () => {
    const user = JSON.parse(localStorage.getItem("user") || "{}");
    const isAdmin = user?.role === "admin";

    const navItems: NavItem[] = [
        { content: "Home", href: "/" },
        { content: "Verify Account", href: "/verify-account" },
        { content: "Dashboard", href: "/dashboard" },
        ...(isAdmin ? [{ content: "Admin Dashboard", href: "admin/dashboard" }] : []),
    ];

    const authButtons: NavItem[] = [
        { content: "Login", href: "/login" },
        { content: "Submit Request", href: "/signup" }
    ];

    return (
        <AuthProvider> {/* ← أضف */}
            <div className="root-layout">
                <NavBar 
                    logo={logoImg}
                    items={navItems} 
                    btn={authButtons} 
                    smallBtn={menuIcon}
                />
                <main>
                    <Outlet /> 
                </main>
                <footer>
                    <Footer 
                        logo={logoImg}
                        items={navItems} 
                        contact={contactData}
                        icons={socialIcons}
                        rights="© 2026 YourBank. All Rights Reserved"
                        p="Privacy Policy"
                        s="Terms of Service"
                    />
                </footer>
            </div>
        </AuthProvider> 
    );
}

export default RootLayout;