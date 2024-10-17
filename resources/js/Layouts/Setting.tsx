import { Link } from "@inertiajs/react"
import {PropsWithChildren} from "react";

export const description =
    "A settings page. The settings page has a sidebar navigation and a main content area. The main content area has a form to update the store name and a form to update the plugins directory. The sidebar navigation has links to general, security, integrations, support, organizations, and advanced settings."

export function SettingLayout({ children }: PropsWithChildren) {
    return (
        <div className="flex min-h-screen w-full flex-col">
            <main className="flex min-h-[calc(100vh_-_theme(spacing.16))] flex-1 flex-col gap-4 bg-muted/40 p-4 md:gap-8 md:p-10">
                <div className="mx-auto grid w-full max-w-6xl gap-2">
                    <h1 className="text-3xl font-semibold">Settings</h1>
                </div>
                <div className="mx-auto grid w-full max-w-6xl items-start gap-6 md:grid-cols-[180px_1fr] lg:grid-cols-[250px_1fr]">
                    <nav
                        className="grid gap-4 text-sm text-muted-foreground" x-chunk="dashboard-04-chunk-0"
                    >
                        <Link href="/dashboard" className="font-semibold text-primary">
                            Dashboard
                        </Link>
                        <Link href="/profile">Profile</Link>
                        <Link href="/setting">Advanced</Link>
                    </nav>
                    <div className="grid gap-6">
                        {children}
                    </div>
                </div>
            </main>
        </div>
    )
}
