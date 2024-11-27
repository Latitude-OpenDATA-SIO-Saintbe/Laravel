import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useEffect } from 'react';
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

/**
 * Register component for user registration.
 *
 * This component renders a registration form that allows users to create an account.
 * It extracts a token from the URL, which is passed from the InviteController, and includes it in the form data.
 *
 * @component
 * @returns {JSX.Element} The rendered registration form component.
 *
 * @example
 * <Register />
 *
 * @remarks
 * The form includes fields for first name, last name, email, password, and password confirmation.
 * It also includes a hidden token field to ensure the token is passed correctly when submitting the form.
 *
 * @function
 * @name Register
 *
 * @requires usePage
 * @requires useForm
 * @requires GuestLayout
 * @requires Head
 * @requires Card
 * @requires CardHeader
 * @requires CardTitle
 * @requires CardDescription
 * @requires CardContent
 * @requires Label
 * @requires Input
 * @requires Button
 * @requires Link
 *
 * @param {FormEventHandler} submit - Handles form submission.
 * @param {object} data - Form data including firstname, lastname, email, password, password_confirmation, and token.
 * @param {function} setData - Function to update form data.
 * @param {function} post - Function to submit form data.
 * @param {boolean} processing - Indicates if the form is being processed.
 * @param {object} errors - Form validation errors.
 * @param {function} reset - Function to reset form fields.
 */
export default function Register() {
    // Extract token from the URL (passed from the InviteController)
    const { token } = usePage().props;

    const { data, setData, post, processing, errors, reset } = useForm({
        firstname: '',
        lastname: '',
        email: '',
        password: '',
        password_confirmation: '',
        token: token || '',  // Token is added here
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        // Ensure that the token is passed correctly when submitting the form
        post(route('register-post', { token: data.token }), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Register" />

            <form onSubmit={submit}>
                <Card className="mx-auto max-w-sm">
                    <CardHeader>
                        <CardTitle className="text-xl">Sign Up</CardTitle>
                        <CardDescription>
                            Enter your information to create an account
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="first-name">First name</Label>
                                    <Input
                                        id="first-name"
                                        value={data.firstname}
                                        onChange={(e) => setData('firstname', e.target.value)}
                                        required
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="last-name">Last name</Label>
                                    <Input
                                        id="last-name"
                                        value={data.lastname}
                                        onChange={(e) => setData('lastname', e.target.value)}
                                        required
                                    />
                                </div>
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    required
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="password">Password</Label>
                                <Input
                                    id="password"
                                    type="password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    required
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">Confirm Password</Label>
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    value={data.password_confirmation}
                                    onChange={(e) => setData('password_confirmation', e.target.value)}
                                    required
                                />
                            </div>
                            {/* Hidden token field */}
                            <input type="hidden" name="token" value={String(data.token)} />

                            <Button type="submit" className="w-full" disabled={processing}>
                                {processing ? 'Creating your account...' : 'Create an account'}
                            </Button>
                        </div>
                        <div className="mt-4 text-center text-sm">
                            <Link
                                href={route('login')}
                                className="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Already have an account?{" "}
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </form>
        </GuestLayout>
    );
}
