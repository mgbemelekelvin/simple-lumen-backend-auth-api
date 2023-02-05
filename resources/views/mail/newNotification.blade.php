@component('mail::message')
    <div style="padding: 5px;">
        <p>
            Hi {{ $data['name'] }},
        </p>
        @if(!empty($data['email_message_1']))
            {!! $data['email_message_1'] !!}
        @endif
        @if(!empty($data['email_button_1']))
        <div style="text-align: center; margin-top:10px; margin-bottom:10px;">
            <a href="{{ $data['email_button_url_1'] }}">
                <button style="text-align: center;background-color: #ff6004; color: white; padding: 20px; border-radius: 8px; text-decoration:none; cursor: pointer;font-size: 16px; border: none; min-width: 50%;">{{ $data['email_button_1'] }}</button>
            </a>
        </div>
        @endif
        @if(!empty($data['email_message_2']))
            {!! $data['email_message_2'] !!}
        @endif
        @if(!empty($data['email_message_3']))
            {!! $data['email_message_3'] !!}
        @endif
    </div>
    UserRegistry
@endcomponent
