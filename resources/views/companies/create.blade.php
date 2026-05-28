@extends('layouts.app') @section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h4>เพิ่มข้อมูลลูกค้าบริษัท (Corporate Customer)</h4>
        </div>
        <div class="card-body">
            
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endcenter
                    </ul>
                </div>
            @endif

            <form action="{{ route('companies.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="name" class="form-label">ชื่อบริษัท/องค์กร <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="mb-3">
                    <label for="branch" class="form-label">สาขา</label>
                    <input type="text" class="form-control" id="branch" name="branch" value="{{ old('branch') }}">
                </div>

                <div class="mb-3">
                    <label for="contact_person" class="form-label">ชื่อผู้ติดต่อ</label>
                    <input type="text" class="form-control" id="contact_person" name="contact_person" value="{{ old('contact_person') }}">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tel_1" class="form-label">เบอร์โทรศัพท์ 1</label>
                        <input type="text" class="form-control" id="tel_1" name="tel_1" value="{{ old('tel_1') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tel_2" class="form-label">เบอร์โทรศัพท์ 2</label>
                        <input type="text" class="form-control" id="tel_2" name="tel_2" value="{{ old('tel_2') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">อีเมล</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                </div>

                <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
                <a href="{{ route('companies.index') }}" class="btn btn-secondary">ยกเลิก</a>
            </form>
        </div>
    </div>
</div>
@endsection