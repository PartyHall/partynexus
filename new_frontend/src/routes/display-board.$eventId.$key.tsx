import { useInterval } from '@/hooks/useInterval';
import { createFileRoute } from '@tanstack/react-router'
import { useEffect, useState } from 'react';

export const Route = createFileRoute('/display-board/$eventId/$key')({
  component: RouteComponent,
})

function RouteComponent() {
  const params = Route.useParams();
  const [pictures, setPictures] = useState([]);

  const fetchPictures = async () => {
    const resp = await fetch(`/api/events/${params.eventId}/pictures?displayBoardKey=${params.key}`, {
      // We want the DISPLAY BOARD KEY to be used and absolutely not
      // the cookies
      // as the amount of picture changes depending on the authenticated user
      // and cookies are used first
      credentials: 'omit',
    });

    const data = await resp.json();
    setPictures(data['member'].map((x: any) => x.id));
  }

  useInterval(() => {
    console.log('Fetching pictures...');
    fetchPictures();
  }, 5000);

  useEffect(() => { fetchPictures(); }, []);

  return <div className='w-[100vw] h-[100vh] p-2 grid grid-cols-3 grid-rows-3 gap-2'>
    {
      pictures.map(x => <img
        key={x}
        src={`/api/pictures/${x}/download?displayBoardKey=${params.key}`}
        alt="Display Board"
        className="w-full h-full object-cover rounded-2xl"
      />)
    }
  </div>
}
