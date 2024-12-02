import React, { useState, ChangeEvent, FormEvent } from 'react';
import { Inertia } from '@inertiajs/inertia';
import { SettingLayout } from '@/Layouts/Setting';
import axios from 'axios';

interface User {
    firstname: string;
    lastname: string;
    email: string;
    role: string;
    manager: {
        name: string;
        role: string;
    } | null;
}

interface ProfileProps {
    user: User;
}

/**
 * Profile component for displaying and updating user profile information.
 *
 * @component
 * @param {ProfileProps} props - The properties for the Profile component.
 * @param {User} props.user - The user object containing profile information.
 *
 * @returns {JSX.Element} The rendered Profile component.
 *
 * @example
 * <Profile user={user} />
 *
 * @typedef {Object} ProfileProps
 * @property {User} user - The user object containing profile information.
 *
 * @typedef {Object} User
 * @property {string} firstname - The first name of the user.
 * @property {string} lastname - The last name of the user.
 * @property {string} email - The email address of the user.
 * @property {string} role - The role of the user.
 * @property {Object} [manager] - The manager object containing manager information.
 * @property {string} manager.name - The name of the manager.
 * @property {string} manager.role - The role of the manager.
 *
 * @typedef {Object} PasswordData
 * @property {string} current_password - The current password of the user.
 * @property {string} new_password - The new password of the user.
 * @property {string} confirm_password - The confirmation of the new password.
 *
 * @typedef {Object} FormData
 * @property {string} firstname - The first name of the user.
 * @property {string} lastname - The last name of the user.
 * @property {string} email - The email address of the user.
 * @property {string} role - The role of the user.
 * @property {Object} [manager] - The manager object containing manager information.
 *
 * @function handleChange
 * @description Handles changes to the profile form inputs.
 * @param {ChangeEvent<HTMLInputElement>} e - The change event.
 *
 * @function handlePasswordChange
 * @description Handles changes to the password form inputs.
 * @param {ChangeEvent<HTMLInputElement>} e - The change event.
 *
 * @function handleSubmit
 * @description Handles the submission of the profile update form.
 * @param {FormEvent} e - The form submission event.
 *
 * @function handlePasswordSubmit
 * @description Handles the submission of the password change form.
 * @param {FormEvent} e - The form submission event.
 *
 * @function handleInviteCreate
 * @description Handles the creation of an invite.
 * @returns {Promise<void>} A promise that resolves when the invite is created.
 */
const Profile: React.FC<ProfileProps> = ({ user }) => {
    const [formData, setFormData] = useState<User>({
        firstname: user.firstname || '',
        lastname: user.lastname || '',
        email: user.email || '',
        role: user.role || '',
        manager: user.manager || null,
    });

        // // Check if the role is an array of objects or a string
        // let roleName: string;

        // if (Array.isArray(user.roles) && user.roles.length > 0) {
        //     roleName = user.roles[0].name;  // If roles is an array of objects
        // } else if (typeof user.roles === 'string') {
        //     roleName = user.roles;  // If roles is a string
        // } else {
        //     roleName = 'No Role Assigned';  // Fallback if roles is empty or undefined
        // }

    const [passwordData, setPasswordData] = useState({
        current_password: '',
        new_password: '',
        confirm_password: '',
    });

    const [inviteStatus, setInviteStatus] = useState<string | null>(null);  // For success/failure messages
    const [email, setEmail] = useState<string>('');  // New state for email input

    // Handle changes to the form inputs
    const handleChange = (e: ChangeEvent<HTMLInputElement>) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value,
        });
    };

    // Handle changes to the password
    const handlePasswordChange = (e: ChangeEvent<HTMLInputElement>) => {
        setPasswordData({
            ...passwordData,
            [e.target.name]: e.target.value,
        });
    };

    // Handle form submission
    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        axios.put('/profile', formData)
        .then((response) => {
            console.log(response.data);
            // reload page
            window.location.reload();
        })
        .catch((error) => {
            console.error(error);
        });
    };

    // Handle password form submission
    const handlePasswordSubmit = (e: FormEvent) => {
        e.preventDefault();
        axios.put('/profile/password/update', passwordData)
        .then((response) => {
            console.log(response.data);
            // reload page
            window.location.reload();
        })
        .catch((error) => {
            console.error(error);
        });
    };

    // Handle invite creation
    const handleInviteCreate = async () => {
        // Make sure email is provided
        if (!email) {
            setInviteStatus('Please provide an email address.');
            return;
        }

        try {
            // Send email to the backend
            const response = await axios.post('/invite/create', {
                email,
            });

            console.log(response.data);
            // If successful, set the success message
            setInviteStatus('Invite created and email sent successfully!');
        } catch (error) {
            console.error(error);
            // If error, display the error message
            setInviteStatus('Failed to create invite. Please try again.');
        }
    };

    return (
        <SettingLayout>
            <div className="max-w-4xl mx-auto py-8 px-6 sm:px-8">
                <h1 className="text-3xl font-bold text-gray-800 mb-6">Profile</h1>

                {/* Profile Update Form */}
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label htmlFor="firstname" className="block text-sm font-medium text-gray-700">
                                First Name
                            </label>
                            <input
                                type="text"
                                id="firstname"
                                name="firstname"
                                value={formData.firstname}
                                onChange={handleChange}
                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            />
                        </div>
                        <div>
                            <label htmlFor="lastname" className="block text-sm font-medium text-gray-700">
                                Last Name
                            </label>
                            <input
                                type="text"
                                id="lastname"
                                name="lastname"
                                value={formData.lastname}
                                onChange={handleChange}
                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            />
                        </div>
                    </div>
                    <div>
                        <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                            Email
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value={formData.email}
                            onChange={handleChange}
                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700">Role</label>
                        <p className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm sm:text-sm">
                            {formData.role || 'No Role Assigned'}
                        </p>
                    </div>

                    {/* Manager Information */}
                    {user.manager && (
                        <div>
                            <label className="block text-sm font-medium text-gray-700">Manager</label>
                            <p className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm sm:text-sm">
                                {user.manager.name} ({user.manager.role})
                            </p>
                        </div>
                    )}
                    <div className="flex justify-end">
                        <button
                            type="submit"
                            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Update Profile
                        </button>
                    </div>
                </form>

                {/* Password Change Form */}
                <div className="mt-12">
                    <h2 className="text-2xl font-bold text-gray-800 mb-4">Change Password</h2>
                    <form onSubmit={handlePasswordSubmit} className="space-y-6">
                        <div>
                            <label htmlFor="current_password" className="block text-sm font-medium text-gray-700">
                                Current Password
                            </label>
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                value={passwordData.current_password}
                                onChange={handlePasswordChange}
                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            />
                        </div>
                        <div>
                            <label htmlFor="new_password" className="block text-sm font-medium text-gray-700">
                                New Password
                            </label>
                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                value={passwordData.new_password}
                                onChange={handlePasswordChange}
                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            />
                        </div>
                        <div>
                            <label htmlFor="confirm_password" className="block text-sm font-medium text-gray-700">
                                Confirm Password
                            </label>
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                value={passwordData.confirm_password}
                                onChange={handlePasswordChange}
                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            />
                        </div>
                        <div className="flex justify-end">
                            <button
                                type="submit"
                                className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            >
                                Change Password
                            </button>
                        </div>
                    </form>
                </div>

                {/* Invite Creation Form */}
                <div className="mt-12">
                    <h2 className="text-2xl font-bold text-gray-800 mb-4">Create Invitation</h2>

                    {/* Email Input for Invite */}
                    <div>
                        <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                            Invite Email Address
                        </label>
                        <input
                            type="email"
                            id="invite_email"
                            name="invite_email"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        />
                    </div>

                    {/* Status Message */}
                    {inviteStatus && (
                        <p className={`text-sm font-semibold ${inviteStatus.includes('success') ? 'text-green-600' : 'text-red-600'}`}>
                            {inviteStatus}
                        </p>
                    )}

                    {/* Button to Create Invite */}
                    <button
                        onClick={handleInviteCreate}
                        className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Create Invitation
                    </button>
                </div>
            </div>
        </SettingLayout>
    );
};

export default Profile;
