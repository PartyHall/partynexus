import { Button, Card, Flex, Tooltip } from "antd";
import { IconCheck } from "@tabler/icons-react";
import KeyVal from "./Keyval";
import { PnSongRequest } from "../sdk/responses/song";

import { useAuth } from "../hooks/auth";
import { useTranslation } from "react-i18next";

export default function RequestedSong({ rs, onDelete }: { rs: PnSongRequest, onDelete: (rs: PnSongRequest) => void }) {
    const { t } = useTranslation();
    const { isGranted } = useAuth();


    return <Card>
        <Flex>
            <Flex vertical flex="1">
                <KeyVal label={t('karaoke.request.song_title')}>{rs.title}</KeyVal>
                <KeyVal label={t('karaoke.request.song_artist')}>{rs.artist}</KeyVal>
                <KeyVal label={t('karaoke.request.requested_by')}>{rs.requestedBy?.username}</KeyVal>
            </Flex>
            {
                isGranted('ROLE_ADMIN')
                && <Flex vertical align="center" justify="center">
                    <Tooltip title={t('karaoke.request.mark_done')}>
                        <Button
                            shape="circle"
                            icon={<IconCheck size={20} />}
                            onClick={() => onDelete(rs)}
                        />
                    </Tooltip>
                </Flex>
            }
        </Flex>
    </Card>
}