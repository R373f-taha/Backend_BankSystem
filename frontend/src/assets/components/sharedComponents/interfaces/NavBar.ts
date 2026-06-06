//path+content:
export interface NavItem {
    content: string;
    href: string;
}
//props:
export interface NavBarProps {
    logo:string,
    items: NavItem[];
    btn: NavItem[];
    smallBtn: string;
}