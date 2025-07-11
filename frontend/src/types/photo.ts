export type DisplayBoardKey = {
    id: number;
    key: string;
    event: string;
    url: string;
};

export type Picture = {
    id: string;
    event: string;
    takenAt: string;
    unattended: boolean;
    applianceUuid: string;
    hasAlternatePicture: boolean;
}