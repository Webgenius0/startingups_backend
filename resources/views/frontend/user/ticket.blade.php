<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Ticket</title>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Urbanist', sans-serif;
        }

        body {
            background-color: #F8F8F8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 375px;
            background: #efe7e7;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: white;
            border-bottom: 1px solid #eee;
        }

        .header button {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
        }

        .header h1 {
            font-size: 18px;
            font-weight: 600;
            color: #2B2B2B;
        }

        .header img {
            width: 20px;
            cursor: pointer;
        }

        /* .ticket-image {
            width: 100%;
            height: 150px;
            background: url('{{ $data['event_cover'] }}') center/cover no-repeat;
        } */

        .ticket-content {
            padding: 15px;
        }

        .user-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .user-info .profile {
            display: flex;
            align-items: center;
        }

        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .user-info .name {
            font-size: 16px;
            font-weight: 600;
            color: #212121;
        }

        .price {
            text-align: right;
        }

        .price .amount {
            font-size: 20px;
            font-weight: 700;
            color: #171717;
        }

        .price .people {
            font-size: 12px;
            color: #A6A444;
        }

        .details {
            margin-top: 15px;
        }

        .details .row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 14px;
        }

        .details .row:not(:last-child) {
            border-bottom: 1px solid #ddd;
        }

        .details .label {
            color: #45474B;
        }

        .details .value {
            font-weight: 600;
            color: #212121;
        }

        .barcode-section {
            text-align: center;
            padding: 20px;
            background: #fff;
        }

        .barcode img {
            width: 80%;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <button>&larr;</button>
            <h1>Event Ticket</h1>
            <img src="https://media.istockphoto.com/id/1486069918/vector/ripped-paper-ticket-for-a-movie-pass-or-a-show-at-the-cinema.jpg?s=612x612&w=0&k=20&c=eNIMUld-ma6DuNok7wTjl8BkpLo9hY4BYjRW7H9Mk64=" alt="Download">
            
        </div>

        <!-- Event Image -->
        <div  class="ticket-image">
            <img style="width: 100%; height:150px" src="https://media.istockphoto.com/id/1486069918/vector/ripped-paper-ticket-for-a-movie-pass-or-a-show-at-the-cinema.jpg?s=612x612&w=0&k=20&c=eNIMUld-ma6DuNok7wTjl8BkpLo9hY4BYjRW7H9Mk64=" alt="Download">
        </div>

        <!-- Ticket Details -->
        <div class="ticket-content">
            {{-- <div class="user-info">
                <div class="profile">
                    <img src="https://media.istockphoto.com/id/1486069918/vector/ripped-paper-ticket-for-a-movie-pass-or-a-show-at-the-cinema.jpg?s=612x612&w=0&k=20&c=eNIMUld-ma6DuNok7wTjl8BkpLo9hY4BYjRW7H9Mk64=" alt="User">
                    <div class="name">{{ $data['user_name'] }}</div>
                </div>
                <div class="price">
                    <div class="amount">${{ number_format($data['price'], 2) }}</div>
                    <div class="people">{{ $data['person_count'] }} Person</div>
                </div>
            </div> --}}

            <!-- Order Details -->
            {{-- <div class="details">
                <div class="row">
                    <span class="label">Event</span>
                    <span class="value">{{ $data['event_name'] ? $data['event_name'] : '' }}</span>
                </div>
                <div class="row">
                    <span class="label">Date</span>
                    <span class="value">{{ $data['date'] ?  \Carbon\Carbon::parse($data['date'])->format('F d, Y') : '' }}</span>
                </div>
                <div class="row">
                    <span class="label">In time</span>
                    <span class="value">{{ $data['in_time'] ? $data['in_time'] : '' }}</span>
                </div>
                <div class="row">
                    <span class="label">Location</span>
                    <span class="value">{{ $data['location'] ? $data['location'] :'' }}</span>
                </div>
                <div class="row">
                    <span class="label">Guests</span>
                    <span class="value">{{ $data['guests'] ?  implode(', ', $data['guests']->toArray()) : '' }}</span>
                </div>
            </div> --}}
        </div>

        <!-- Barcode -->
        <div class="barcode-section">
            <img src="https://barcode.tec-it.com/barcode.ashx?data={{ $data['booking_id'] }}&code=Code128&dpi=96" alt="Barcode">
        </div>
    </div>

</body>
</html>
