import { Image } from 'antd';
import PLACEHOLDER from '../assets/placeholder.webp';
import PnPicture from '../sdk/responses/picture';

export default function PictureCard({picture}: {picture: PnPicture}) {
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
        fallback={PLACEHOLDER}
        width={200}
    />
}