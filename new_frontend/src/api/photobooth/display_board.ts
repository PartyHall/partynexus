import type { DisplayBoardKey } from "@/types/photo";
import { customFetch } from "../customFetch";

export default async function createDisplayBoardLink(eventId: string): Promise<DisplayBoardKey> {
    const resp = await customFetch(
        `/api/display_board_keys`,
        {
            method: 'POST',
            body: JSON.stringify({ event: `/api/events/${eventId}` }),
        },
    );

    return await resp.json();
}