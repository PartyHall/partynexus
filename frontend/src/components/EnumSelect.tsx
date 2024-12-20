import Loader from './Loader';

import { Select } from 'antd';
import { useAsyncEffect } from 'ahooks';
import { useAuth } from '../hooks/auth';
import { useState } from 'react';

/**
 * @TODO later
 * We should make endpoints for enums so that the frontend knows each value automatically
 * Though this raise an issue as it serialize values as their IRI which we DONT WANT
 * since thats an ENUM
 */

type Props = {
    enumName: string;
    disabled?: boolean;
};

export default function EnumSelect({ enumName, disabled }: Props) {
    const [enumValues, setEnumValues] = useState<[] | null>(null);
    const { api } = useAuth();

    useAsyncEffect(async () => {
        const resp = await api.get('/api/' + enumName);
        const data = await resp.json();

        setEnumValues(
            data['member'].map((x: any) => ({
                value: x['value'],
                label: x['name'],
            }))
        );
    }, [enumName]);

    return (
        <Loader loading={enumValues === null}>
            {enumValues && <Select disabled={disabled} options={enumValues} />}
        </Loader>
    );
}
