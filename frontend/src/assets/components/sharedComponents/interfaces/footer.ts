

export interface FooterNavItem {
    content: string;
    href: string;
}

export interface ContactItem {
    icon: string;
    content: string;
}

export interface SocialIcon {
    content: string; 
    href: string;    
}

export interface FooterProps {
    logo: string;
    items: FooterNavItem[];
    contact: ContactItem[];
    icons: SocialIcon[];
    rights: string;
    p: string; 
    s: string; 
}