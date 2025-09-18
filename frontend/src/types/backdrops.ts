export type BackdropAlbum = {
  "@id": string;
  id: number;
  title: string;
  author: string;
  version: number;
  backdrops: Backdrop[];
};

export type Backdrop = {
  "@id": string;
  id: number;
  title: string;
  url: string;
};
