<!DOCTYPE html>

<head>
    <title>Upload JSON Tín Chỉ</title>
</head>

<body>
    <style>
        form {
            padding: 1rem;
        }

        h1 {
            font-size: 1.5em;
        }

        .input {
            border: 1px solid #ccc;
            margin-top: 1rem;
            padding-bottom: .25rem;
            border: none;
            border-bottom: thin solid black;
            outline: none;
        }

        .submit {
            margin-top: 1rem;
        }

        pre {
            display: inline-block;
            background: rgb(202, 202, 202);
            padding: 1rem;
            border-radius: 1rem;
        }
    </style>
    <form action="" method="post" enctype="multipart/form-data">
        <h1>Upload JSON Tín Chỉ</h1>
        {{ csrf_field() }}
        <table>
            <tbody>
                <tr>
                    <td>JSON</td>
                    <td><input class="input" type="file" name="json" accept=".json" required><br /></td>
                </tr>
                <tr>
                    <td>EXCEL</td>
                    <td><input class="input" type="file" name="excel" accept=".xls,.xlsx" required><br /></td>
                </tr>
            </tbody>
        </table>
        <input class="input" type="password" name="password" placeholder="Nhập mật khẩu" required><br />
        <input class="submit" type="submit" value="Upload" />
        @if ($error = session('error'))
            <div style="color: red; display: block; margin-top: 1rem">
                {{ $error }}
            </div>
        @endif
        @if ($error = session('success'))
            <div style="color: green; display: block; margin-top: 1rem">
                {{ $error }}
            </div>
        @endif
        <div style="margin-top: 1rem;">
            <span>Example of json file:</span><br>
<pre>{
    "title": "Học kỳ n năm học 20xx - 20xx",
    "data": [
        [
            "Chủ nghĩa xã hội khoa học-2-21 (A18C6D501)",
            4,
            "06/06/22",
            "10/07/22",
            "1->3"
        ],
        [
            "Chủ nghĩa xã hội khoa học-2-21 (A18C6D501)",
            6,
            "06/06/22",
            "10/07/22",
            "1->3"
        ],
        ...
    ]
}</pre>
        </div>
    </form>
</body>
