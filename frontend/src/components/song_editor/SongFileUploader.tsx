import { Button, Flex, Typography } from "antd";
import PnSong from "../../sdk/responses/song";

type Props = {
    type: string;
    song: PnSong;
}

export default function SongFileUploader({type, song}: Props) {
    /**
     * @TODO: If a file is already uploaded, we should be able to listen to it
     * (Or view it if its a video)
     */
    return <Flex gap={8}>
        <Typography>{type}</Typography>
        <Button>Choose...</Button>
    </Flex>
}