import requests
from pathlib import Path
from datetime import datetime
from typing import List

def generate_intermediate_datetimes(start: datetime, end: datetime, x: int) -> List[datetime]:
    return [start + i * ((end - start) / (x + 1)) for i in range(1, x + 1)]

def upload_picture(file: Path, date: datetime, unattended: bool):
    print(f'[{"UNATTENDED" if unattended else "HANDTAKEN"}] Uploading {file.name} taken on {date.isoformat()}')

    resp = requests.post(
        'http://localhost/api/pictures',
        headers={
            'X-HARDWARE-ID': 'b094786e-5158-4ceb-861b-28cb45b2a2c3',
            'X-API-TOKEN': 'my-api-token',
        },
        data={
            'applianceUuid': 'd7787de5-d527-4f11-bbde-523ab6110cbf',
            'unattended': 'true' if unattended else 'false',
            'takenAt': date.isoformat(),
            'event': '/api/events/0192bf5a-67d8-7d9d-8a5e-962b23aceeaa',
        },
        files={
            'file': ('file.jpg', file.open('rb'), 'image/jpeg'),
        },
    )

    resp.raise_for_status()

def upload_pictures(pict_path: Path, start_at: datetime, end_at: datetime, unattended: bool):
    pictures = sorted([file for file in pict_path.iterdir() if file.suffix.lower() == '.jpg' and file.is_file()], key=lambda x: x.name.lower())
    pictures_dates = generate_intermediate_datetimes(start_at, end_at, len(pictures))
    for x, file in enumerate(pictures):
        upload_picture(
            file,
            pictures_dates[x],
            unattended,
        )

bp = Path('./0_DATA/pictures')
up = Path('./0_DATA/pictures/unattended')
if not bp.exists() or not up.exists():
    raise "You should have a folder located at ./0_DATA/pictures that contains all the handtaken pictures and a ./0_DATA/pictures/unattended where you put all unattended pictures."

start_at = datetime(2022, 12, 31, 20, 00, 00)
end_at = datetime(2023, 1, 1, 5, 00, 00)

upload_pictures(bp, start_at, end_at, False)
upload_pictures(up, start_at, end_at, True)
