import { useAuthStore } from "@/stores/auth";
import { IconCalendar, IconMenu2, IconMicrophone, IconSettings, IconUser } from "@tabler/icons-react";
import { Link, useNavigate } from "@tanstack/react-router";
import { useState } from "react";
import { useTranslation } from "react-i18next";
import Button from "./generic/button";

export function TopBar() {
    const { t } = useTranslation();
    const navigate = useNavigate();
    const isGranted = useAuthStore((state) => state.isGranted);

    const [opened, setOpened] = useState<boolean>(false);

    let links = [
        { to: "/", label: t('events.title'), icon: <IconCalendar /> },
        { to: "/karaoke", label: "Karaoke", icon: <IconMicrophone /> },
        { to: "/account", label: t('account.title'), icon: <IconUser /> },
    ];

    if (isGranted("ROLE_ADMIN")) {
        links = [
            ...links.slice(0, 2),
            { to: "/admin", label: t('admin.title'), icon: <IconSettings /> },
            ...links.slice(2),
        ];
    }

    return <nav className="bg-synthbg-800">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="flex items-center justify-between h-16">
                <div className="flex-shrink-0">
                    <img
                        src="/assets/ph_logo_sd.webp"
                        alt="PartyHall logo"
                        className="w-35 cursor-pointer"
                        onClick={() => navigate({ to: "/" })}
                    />
                </div>
                <div className="hidden md:block">
                    <div className="ml-10 flex items-center space-x-4">
                        {
                            links.map((link) => (
                                <Link
                                    key={link.to}
                                    to={link.to}
                                    className="flex gap-1 no-hover hover:text-pink-glow"
                                >
                                    {link.icon}{link.label}
                                </Link>
                            ))
                        }
                    </div>
                </div>
                <div className="md:hidden">
                    <Button id="menu-toggle" className="focus:outline-none" onClick={() => setOpened(!opened)}>
                        <IconMenu2 />
                    </Button>
                </div>
            </div>
        </div>

        <div id="mobile-menu" className={`md:hidden ${opened ? '' : 'hidden'} px-2 pt-2 pb-3 space-y-1 flex flex-col gap-2 items-center fixed w-full bg-synthbg-800 z-1000`}>
            {
                links.map((link) => (
                    <Link
                        key={link.to}
                        to={link.to}
                        className="flex gap-1 no-hover hover:text-pink-glow"
                        onClick={() => setOpened(false)}
                    >
                        {link.icon}
                        {link.label}
                    </Link>
                ))
            }
        </div>
    </nav>;
}