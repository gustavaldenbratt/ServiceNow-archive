import { Form, Head } from '@inertiajs/react';
import ConnectionController from '@/actions/App/Http/Controllers/ServiceNow/ConnectionController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/servicenow/connection';

type Connection = {
    instance_url: string | null;
    username: string | null;
    has_password: boolean;
};

export default function Connection({ connection }: { connection: Connection }) {
    return (
        <>
            <Head title="ServiceNow connection" />

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Connection"
                    description="Set the ServiceNow instance and service account used to fetch records"
                />

                <Form
                    {...ConnectionController.update.form()}
                    options={{ preserveScroll: true }}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="instance_url">
                                    Instance URL
                                </Label>
                                <Input
                                    id="instance_url"
                                    name="instance_url"
                                    defaultValue={
                                        connection.instance_url ?? ''
                                    }
                                    placeholder="https://your-instance.service-now.com"
                                    required
                                />
                                <InputError message={errors.instance_url} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="username">Username</Label>
                                <Input
                                    id="username"
                                    name="username"
                                    defaultValue={connection.username ?? ''}
                                    required
                                />
                                <InputError message={errors.username} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">Password</Label>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    placeholder={
                                        connection.has_password
                                            ? 'Leave blank to keep the current password'
                                            : ''
                                    }
                                />
                                <InputError message={errors.password} />
                            </div>

                            <Button disabled={processing}>Save</Button>
                        </>
                    )}
                </Form>

                <Form {...ConnectionController.test.form()}>
                    {({ processing }) => (
                        <Button
                            type="submit"
                            variant="outline"
                            disabled={processing}
                        >
                            Test connection
                        </Button>
                    )}
                </Form>
            </div>
        </>
    );
}

Connection.layout = {
    breadcrumbs: [{ title: 'Connection', href: edit() }],
};
