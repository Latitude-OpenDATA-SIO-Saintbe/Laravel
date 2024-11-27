import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

// Modal component for email input
const Modal = ({ isOpen, onClose }: { isOpen: boolean; onClose: () => void }) => {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('password.email'), {
            onSuccess: () => {
                alert('Password reset link sent!');
                reset();  // Clear the email field on success
                onClose();  // Close the modal
            },
            onError: () => {
                alert('Unable to send reset link. Please check your email or try again.');
            },
        });
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-gray-500 bg-opacity-75 flex justify-center items-center z-50">
            <div className="bg-white p-6 rounded-lg w-96">
                <h2 className="text-lg font-semibold mb-4">Reset Password</h2>
                <form onSubmit={submit}>
                    <div className="mb-4">
                        <Label htmlFor="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            placeholder="Enter your email"
                            required
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                        />
                        {errors.email && <div className="text-red-600 mt-2">{errors.email}</div>}
                    </div>
                    <div className="flex justify-end space-x-2">
                        <Button type="button" onClick={onClose} disabled={processing}>Cancel</Button>
                        <Button type="submit" disabled={processing}>Send Reset Link</Button>
                    </div>
                </form>
            </div>
        </div>
    );
};

/**
 * Login component for user authentication.
 */
export default function Login({ status }: { status?: string }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const [isModalOpen, setIsModalOpen] = useState(false);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Log in" />
            {status && <div className="mb-4 text-sm font-medium text-green-600">{status}</div>}
            <form onSubmit={submit}>
                <Card className="w-full max-w-sm">
                    <CardHeader>
                        <CardTitle className="text-2xl">Login</CardTitle>
                        <CardDescription>Enter your email to login.</CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4">
                        <div>
                            <Label htmlFor="email">Email</Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                            />
                            {errors.email && <div className="text-red-600">{errors.email}</div>}
                        </div>
                        <div>
                            <Label htmlFor="password">Password</Label>
                            <Input
                                id="password"
                                type="password"
                                required
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                            />
                            {errors.password && <div className="text-red-600">{errors.password}</div>}
                        </div>
                    </CardContent>
                    <CardFooter className="flex flex-col">
                        <Button type="submit" disabled={processing} className="w-full">
                            Sign in
                        </Button>
                        <div className="mt-4 text-center">
                            <button
                                type="button"
                                onClick={() => setIsModalOpen(true)}
                                className="text-sm text-blue-600 hover:underline"
                            >
                                Forgot your password?
                            </button>
                        </div>
                    </CardFooter>
                </Card>
            </form>

            {/* Modal for password reset */}
            <Modal isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} />
        </GuestLayout>
    );
}
