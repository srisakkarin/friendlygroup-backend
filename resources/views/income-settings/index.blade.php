@extends('include.app')
@section('header')
<script src="{{ asset('asset/script/incomeSetting.js') }}"></script>
@endsection

@section('content')
<div class="container">
    <h3 class="mb-4">{{__('app.income_settings')}}</h3>

    <form id="income-settings-form">
        @csrf
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>{{ __('app.Title')}}</th>
                    <th>{{__('app.Company')}}</th>
                    <th>{{__('app.Customer')}}</th>
                    <th>{{__('app.calculate_with')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rules as $rule)
                <tr>
                    <td>{{ $rule->title }}</td>
                    <td>
                        <input type="number"
                            name="rules[{{ $rule->id }}][company_percent]"
                            class="form-control percent-input"
                            value="{{ $rule->company_percent }}"
                            min="0" max="100" required>
                    </td>
                    <td>
                        <input type="number"
                            name="rules[{{ $rule->id }}][customer_percent]"
                            class="form-control percent-input"
                            value="{{ $rule->customer_percent }}"
                            min="0" max="100" required>
                    </td>
                    <td>
                        <!-- enum('fixed','percentage') selecter -->
                    <td>
                        <select name="rules[{{ $rule->id }}][calculate_with]" class="form-control">
                            <option value="fixed" {{ $rule->calculate_with == 'fixed' ? 'selected' : '' }}>Fixed</option>
                            <option value="percentage" {{ $rule->calculate_with == 'percentage' ? 'selected' : '' }}>Percentage</option>
                        </select>
                    </td>

                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">{{__('app.Save Changes')}}</button>
        </div>
    </form>
</div>
@endsection