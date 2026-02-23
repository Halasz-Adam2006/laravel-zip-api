<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Alphabet Export</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 4px;
        }

        h2 {
            font-size: 14px;
            margin: 12px 0 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #f3f4f6;
        }
    </style>
</head>

<body>
    <h1>Alphabet Export</h1>
    <div><strong>County:</strong> {{ $county }}</div>
    <div><strong>Letter:</strong> {{ $letter }}</div>

    <h2>Cities</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>City</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($cities as $index => $city)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $city }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">No cities found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>