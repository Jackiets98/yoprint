<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Upload</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .fade-out {
            transition: opacity 1s ease-out;
            opacity: 0;
        }
        .upload-entry {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .upload-entry .filename {
            flex: 1;
        }
        .upload-entry .badge {
            margin-left: 1rem;
        }
        .upload-entry small {
            margin-left: 1rem;
            white-space: nowrap;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="card p-4 shadow-sm">
        <h2 class="mb-3">CSV File Upload</h2>
        <form method="POST" enctype="multipart/form-data" action="{{ route('uploads.store') }}">
            @csrf
            <div class="input-group mb-3">
                <input type="file" name="file" class="form-control" required>
                <button class="btn btn-primary" type="submit">Upload</button>
            </div>
        </form>

        @if(session('success'))
            <div id="upload-success" class="alert alert-success mt-2">{{ session('success') }}</div>
        @endif
        @if(session('info'))
            <div class="alert alert-info mt-2">{{ session('info') }}</div>
        @endif

        <hr>

        <h4 class="mb-3">Upload History</h4>
        <div id="upload-list">Loading...</div>
    </div>
</div>

<script>
    function timeAgo(timestamp) {
        const now = new Date();
        const then = new Date(timestamp);
        const secondsAgo = Math.floor((now - then) / 1000);

        const intervals = [
            { label: 'year', seconds: 31536000 },
            { label: 'month', seconds: 2592000 },
            { label: 'day', seconds: 86400 },
            { label: 'hour', seconds: 3600 },
            { label: 'minute', seconds: 60 },
            { label: 'second', seconds: 1 },
        ];

        for (const interval of intervals) {
            const count = Math.floor(secondsAgo / interval.seconds);
            if (count >= 1) {
                return `${count} ${interval.label}${count !== 1 ? 's' : ''} ago`;
            }
        }
        return 'just now';
    }

    function fetchUploads() {
    fetch("{{ route('uploads.status') }}")
        .then(res => res.json())
        .then(data => {
            const list = data.data.map(row => {
                const badgeClass = row.status === 'Completed' ? 'bg-success'
                                : row.status === 'Failed' ? 'bg-danger'
                                : 'bg-info';
                const fileUrl = `/storage/uploads/${row.path}`;
                const agoText = timeAgo(row.uploaded_at);
                return `<li class="list-group-item upload-entry">
                    <a class="filename" href="${fileUrl}" target="_blank">${row.filename}</a>
                    <span class="badge ${badgeClass}">${row.status}</span>
                    <small class="text-muted">${row.uploaded_at} (${agoText})</small>
                </li>`;
            });

            document.getElementById('upload-list').innerHTML = '<ul class="list-group">' + list.join('') + '</ul>';

            const statusList = data.data.map(row => row.status);
            const successDiv = document.getElementById('upload-success');

            if (successDiv) {
                if (statusList.includes('Completed')) {
                    successDiv.textContent = 'Upload Completed';
                } else if (statusList.includes('Failed')) {
                    successDiv.textContent = 'Upload Failed';
                    successDiv.classList.remove('alert-success');
                    successDiv.classList.add('alert-danger');
                }

                setTimeout(() => {
                    successDiv.classList.add('fade-out');
                }, 1000);
                setTimeout(() => {
                    successDiv.remove();
                }, 3000);
            }
        });
}
    setInterval(fetchUploads, 3000);
    fetchUploads();
</script>
</body>
</html>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>