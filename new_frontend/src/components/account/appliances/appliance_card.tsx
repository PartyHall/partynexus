import { customFetch } from "@/api/customFetch";
import Button, { ButtonLink } from "@/components/generic/button";
import Modal from "@/components/generic/modal";
import Title from "@/components/generic/title";
import { Tooltip } from "@/components/generic/tooltip";
import type { Appliance } from "@/types/appliance";
import { IconEdit, IconTrash } from "@tabler/icons-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

type Props = {
    appliance: Appliance;
    doInvalidateRouter?: () => void;
};

export default function ApplianceCard({ appliance, doInvalidateRouter }: Props) {
    const { t } = useTranslation();

    const [deleteModalOpen, setDeleteModalOpen] = useState(false);
    const [isDeleting, setIsDeleting] = useState(false);

    const deleteAppliance = async () => {
        setIsDeleting(true);
        try {
            await customFetch(`/api/appliances/${appliance.id}`, { method: 'DELETE' });
        } catch (error) {
            console.error('Error deleting appliance:', error);
            alert(t('account.my_appliances.delete_error'));
        } finally {
            setIsDeleting(false);
            setDeleteModalOpen(false);

            if (doInvalidateRouter) {
                doInvalidateRouter();
            }
        }
    };

    return <div className="w-full bg-synthbg-700 p-3 rounded-lg flex flex-row justify-between items-center">
        <Title level={3} noMargin>{appliance.name}</Title>
        <div className="flex flex-row items-center gap-2">
            <Tooltip content={t('generic.edit')}>
                <ButtonLink
                    to="/account/appliances/$id"
                    params={{ id: '' + appliance.id }}
                >
                    <IconEdit />
                </ButtonLink>
            </Tooltip>
            <Tooltip content={t('generic.delete')}>
                <Button onClick={() => setDeleteModalOpen(true)}>
                    <IconTrash />
                </Button>
            </Tooltip>

            <Modal
                open={deleteModalOpen}
                onOpenChange={(open: boolean) => {
                    if (isDeleting) {
                        return;
                    }

                    setDeleteModalOpen
                }}
                title={t('account.my_appliances.delete')}
                description={t('account.my_appliances.delete_confirm', { name: appliance.name })}
                actions={<>
                    <Button
                        variant="secondary"
                        disabled={isDeleting}
                        onClick={() => setDeleteModalOpen(false)}
                    >
                        {t('generic.cancel')}
                    </Button>
                    <Button
                        variant="danger"
                        onClick={deleteAppliance}
                        disabled={isDeleting}
                    >
                        {t('generic.delete')}
                    </Button>
                </>}
            />
        </div>
    </div>;
}