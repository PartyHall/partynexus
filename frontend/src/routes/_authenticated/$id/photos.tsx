import { getPicturesForEvent } from "@/api/events/pictures";
import { createFileRoute } from "@tanstack/react-router";
import { useState } from "react";

import { Lightbox } from "yet-another-react-lightbox";
import {
  Counter,
  Fullscreen,
  Download,
  Zoom,
} from "yet-another-react-lightbox/plugins";
import "yet-another-react-lightbox/styles.css";
import "yet-another-react-lightbox/plugins/counter.css";
import dayjs from "dayjs";

export const Route = createFileRoute("/_authenticated/$id/photos")({
  component: RouteComponent,
  loader: async ({ params }) => {
    if (!params.id) {
      throw new Error("Missing required parameter: id");
    }

    /**
     * @TODO later: we will need an infinite scroll
     * @TODO: Add @tanstack/react-virtual to help with picture loading
     * => Not doing it right now as I made it responsive with different number of columns
     * so its a bit of a pain as tanstack expects a known number of rows
     */

    return await getPicturesForEvent(params.id, false);
  },
});

function RouteComponent() {
  const pictures = Route.useLoaderData();

  const [shownPicture, setShownPicture] = useState<number | null>(null);

  return (
    <div className="grid grid-cols-1 gap-0.5 gallery-md:grid-cols-3 gallery-xs:grid-cols-2">
      {pictures.member.map((picture, idx) => (
        <img
          src={`/api/pictures/${picture.id}/thumbnail`}
          alt={`Picture taken at ${picture.takenAt}`}
          key={picture.id}
          className="w-full aspect-square object-cover"
          onClick={() => setShownPicture(idx)}
        />
      ))}

      <Lightbox
        open={shownPicture !== null}
        close={() => setShownPicture(null)}
        plugins={[Counter, Fullscreen, Download, Zoom]}
        zoom={{ maxZoomPixelRatio: 200 }}
        slides={pictures.member.map((picture) => ({
          src: `/api/pictures/${picture.id}/download`,
          alt: `Picture taken at ${dayjs(picture.takenAt).format("L - LT")}`,
        }))}
        index={shownPicture ?? 0}
      />
    </div>
  );
}
