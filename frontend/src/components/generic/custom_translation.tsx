import { Trans } from "react-i18next";

type Props = {
    mapping: string;
    values?: Record<string, any>;
};

export default function Translate({mapping, values}: Props) {
    return <Trans
        i18nKey={mapping}
        values={values}
        components={{
            blueGlow: <span className='text-blue-glow' />,
            purpleGlow: <span className='text-purple-glow' />,
            redGlow: <span className='text-red-glow' />,
            pinkGlow: <span className='text-pink-glow' />,
            yellowGlow: <span className='text-yellow-glow' />,
            greenGlow: <span className='text-green-glow' />,
            whiteGlow: <span className='text-white-glow' />,
            goldGlow: <span className='text-gold-glow' />,
            lemonGreenGlow: <span className='text-lemon-green-glow' />,
        }}
    />
}