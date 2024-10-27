import EventEditor from "../components/event_editor/EventEditor";

import { useTitle } from "ahooks";
import { useTranslation } from "react-i18next";

export default function NewEventPage() {
    const {t} = useTranslation();
    useTitle(t('event.editor.create_title') + ' - PartyHall');

    return <EventEditor event={null}/>;
}