import '../assets/css/index.css';
import { Button, Select } from 'antd';
import { Controller, useForm } from 'react-hook-form';
import Cookie from 'js-cookie';
import Loader from '../components/Loader';
import PhLogo from '../assets/ph_logo_sd.webp';
import { useAsyncEffect } from 'ahooks';
import { useParams } from 'react-router-dom';
import { useState } from 'react';


/**
 * 
 * This is ugly, not translated or anything
 * but its a one-time use since the frontend is being rewritten
 * 
 */

export default function RegisterPage() {
    const { token } = useParams();
    const [readyToRegister, setReadyToRegister] = useState<boolean>(false);

    const [event, setEvent] = useState<{ id: string, name: string } | null>(null);
    const [loading, setLoading] = useState<boolean>(true);
    const [loadErr, setLoadErr] = useState<string | null>(null);

    const [globalFormError, setGlobalFormError] = useState<string | null>(null);

    useAsyncEffect(async () => {
        setLoadErr(null);
        setLoading(true);

        if (!token) {
            setLoadErr('no_token');
            setLoading(false);
            return;
        }

        try {
            const response = await fetch(`/api/register/${token}`);
            if (!response.ok) {
                throw new Error('Failed to fetch event');
            }

            const data = await response.json();
            setEvent(data);
        } catch (error) {
            console.error('Error fetching event:', error);
            setLoadErr('fetch_error');
        }

        setLoading(false);
    }, [token])

    const {
        control,
        handleSubmit,
        register,
        watch,
    } = useForm({
        defaultValues: {
            username: '',
            firstname: '',
            lastname: '',
            email: '',
            password: '',
            password2: '',
            language: 'fr_FR',
        },
    });

    const password = watch('password');
    const password2 = watch('password2');

    const onSubmit = async (data: any) => {
        setGlobalFormError(null);

        const resp = await fetch(`/api/register/${token}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                username: data.username,
                firstname: data.firstname,
                lastname: data.lastname,
                email: data.email,
                newPassword: data.password,
                language: data.language,
            }),
        });

        if (!resp.ok) {
            const errorData = await resp.json();

            if (resp.status === 422) {
                const violations = errorData['violations'];

                setGlobalFormError(
                    violations?.map((v: any) => '[' + v.propertyPath + ']: ' + v.message).join(', ')
                );

                return;
            }

            setGlobalFormError(errorData.message || 'Une erreur est survenue lors de l\'inscription.');
            return;
        }

        const result = await resp.json();

        Cookie.set('partynexus_jwt', result.token);
        Cookie.set('mercureAuthorization', result.token);
        localStorage.setItem('token', result.token);
        localStorage.setItem('refresh_token', result.refresh_token);
    
        window.location.href = `/events/${event?.id}`;
    };

    return <div className='w-full h-full flex items-center justify-center'>
        <div className="text-center rounded-lg bg-synthbg-900 text-primary-100 m-2 p-4 w-full sm:w-150 flex flex-col gap-4">
            <Loader loading={loading}>
                <img src={PhLogo} alt="Partyhall logo" className='h-10 m-auto' />
                {
                    !readyToRegister
                    && !loadErr
                    && <>
                        <p>
                            Vous allez vous inscrire à PartyHall, via ce lien vous aurez automatiquement accès à l&apos;évènement <span className='blue-glow font-bold'>&quot;{event?.name}&quot;</span>
                        </p>
                        <p>
                            Si vous êtes déjà inscrit, ne continuez pas et allez directement sur votre compte. Si vous n&apos;avez pas accès ou si vous rencontrez un problème, demandez à <span className="red-glow font-bold">Nathan</span>.
                        </p>

                        <Button
                            type="primary"
                            className="mt-4"
                            onClick={() => setReadyToRegister(true)}
                        >
                            Continuer
                        </Button>
                    </>
                }

                {
                    readyToRegister
                    && !loadErr
                    && <form className='flex flex-col gap-4' onSubmit={handleSubmit(onSubmit)}>
                        <div className="flex flex-col gap-1">
                            <label htmlFor="username" className="text-left">Nom d&apos;utilisateur (Ne pourra pas être changé)</label>
                            <input
                                type="text"
                                id="username"
                                {...register('username', { required: true })}
                                className="p-2 rounded bg-synthbg-800 text-primary-100"
                                required
                            />
                        </div>
                        <div className="flex flex-col gap-1">
                            <label htmlFor="firstname" className="text-left">Prénom</label>
                            <input
                                type="text"
                                id="firstname"
                                {...register('firstname', { required: true })}
                                className="p-2 rounded bg-synthbg-800 text-primary-100"
                                required
                            />
                        </div>
                        <div className="flex flex-col gap-1">
                            <label htmlFor="lastname" className="text-left">Nom de famille (Optionnel)</label>
                            <input
                                type="text"
                                id="lastname"
                                {...register('lastname')}
                                className="p-2 rounded bg-synthbg-800 text-primary-100"
                            />
                        </div>
                        <div className="flex flex-col gap-1">
                            <label htmlFor="email" className="text-left">Adresse e-mail</label>
                            <input
                                type="email"
                                id="email"
                                {...register('email', { required: true })}
                                className="p-2 rounded bg-synthbg-800 text-primary-100"
                                required
                            />
                        </div>
                        <div className="flex flex-col gap-1">
                            <label htmlFor="password" className="text-left">Mot de passe</label>
                            <input
                                type="password"
                                id="password"
                                {...register('password', { required: true })}
                                className="p-2 rounded bg-synthbg-800 text-primary-100"
                                required
                            />
                        </div>
                        <div className="flex flex-col gap-1">
                            <label htmlFor="password2" className="text-left">Répétez votre mot de passe</label>
                            <input
                                type="password"
                                id="password2"
                                {...register('password2', { required: true })}
                                className="p-2 rounded bg-synthbg-800 text-primary-100"
                                required
                            />
                        </div>

                        <div className="flex flex-col gap-1">
                            <label htmlFor="language" className="text-left">Langue</label>
                            <Controller
                                name="language"
                                control={control}
                                render={({ field }) => (
                                    <Select
                                        className='w-full'
                                        options={[
                                            { value: 'en_US', label: 'English (American)' },
                                            { value: 'fr_FR', label: 'Français' },
                                        ]}
                                        {...field}
                                        value={field.value}
                                        onChange={field.onChange}
                                    />
                                )}
                            />
                        </div>

                        {
                            (password.length > 0 && password2.length > 0 && password !== password2)
                            && <p className="text-red-500">Les mots de passe ne correspondent pas.</p>
                        }

                        {
                            globalFormError
                            && <p className="text-red-500">{globalFormError}</p>
                        }

                        <button
                            type="submit"
                            className="mt-4 p-2 bg-primary-500 text-white rounded hover:bg-primary-600 transition-colors w-full"
                        >
                            S&apos;inscrire
                        </button>
                    </form>
                }

                {
                    loadErr
                    && <p className="text-red-500">Erreur lors du chargement de l&apos;évènement: {loadErr}</p>
                }
            </Loader>
        </div>
    </div >;
}