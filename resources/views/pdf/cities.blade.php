<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cities in {{ $county->name }} starting with {{ $letter }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #007bff;
            color: white;
            padding: 10px;
            text-align: left;
            border: 1px solid #0056b3;
        }
        td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #e8f4f8;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Cities in {{ $county->name }} County Starting with "{{ $letter }}"</h1>
    
    @if($cities->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Postal Code</th>
                    <th>City Name</th>
                    <th>County</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cities as $city)
                    <tr>
                        <td>{{ $city->zip }}</td>
                        <td>{{ $city->city }}</td>
                        <td>{{ $city->county->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="footer">
            <p>Total cities: {{ $cities->count() }}</p>
            <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
        </div>
    @else
        <p>No cities found starting with "{{ $letter }}" in {{ $county->name }} county.</p>
    @endif
</body>
</html>
