import { Image } from 'antd';
import PnPicture from '../sdk/responses/picture';

const PLACEHOLDER = 'https://placehold.co/1920x1080/171520/d72793/png';

export default function PictureCard({picture}: {picture: PnPicture}) {
    /** 
     * @TODO Disable mirror icons + add a download button
     */
    return <Image
        src={`/api/pictures/${picture.id}/download`}
        alt="Photobooth picture"
        placeholder={
            <Image
                preview={false}
                src={PLACEHOLDER} // @TODO: Generate thumbnail server-side
                width={200}
            />
        }
        fallback={PLACEHOLDER} // @TODO: Embed base64 directly
        width={200}
    />
}