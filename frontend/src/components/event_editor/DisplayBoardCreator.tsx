import DisplayBoardKey from "../../sdk/responses/display_board_key";
import { Flex } from "antd";
import Loader from "../Loader";
import { PnEvent } from "../../sdk/responses/event";

import { useAuth } from "../../hooks/auth";
import { useState } from "react";
import { useTranslation } from "react-i18next";

type Props = {
    event: PnEvent;
};

export default function DisplayBoardCreator({ event }: Props) {
    const { t } = useTranslation();
    const { api } = useAuth();

    const [isCreating, setIsCreating] = useState<boolean>(false);
    const [created, setCreated] = useState<DisplayBoardKey | null>(null);
    const [err, setErr] = useState<string | null>(null);

    const createDisplayBoard = async () => {
        if (isCreating) {
            return;
        }

        setIsCreating(true);

        try {
            setCreated(
                await api.displayBoards.create(event.id),
            );
        } catch (e) {
            setErr('Failed to create display board key');
            console.error(e);
        }

        setIsCreating(false);
    };

    if (event.displayBoardKey) {
        return <></>;
    }

    return <Flex align="center" justify="center" vertical gap={8}>

        {
            !created
            && <Loader loading={isCreating}>
                <a onClick={createDisplayBoard}>
                    {t('event.display_board.create')}
                </a>
            </Loader>
        }

        {
            created
            && <span style={{ marginLeft: 8 }}>
                {t('event.display_board.created')}
            </span>
        }

        {
            err
            && <span style={{ color: 'red', marginLeft: 8 }}>
                {t('event.display_board.error')}
            </span>
        }
    </Flex>
}