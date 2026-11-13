<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">{{__('Add New Staff')}}</h3>

            <!--begin::Close-->
            <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                 aria-label="Close">
                <i class="ki-duotone ki-cross fs-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
            </div>
            <!--end::Close-->
        </div>

        <form method="POST" action="{{route('admin.staffs.store')}}">
            @csrf


            <div class="modal-body">
                <div class="mb-5">
                    <div class="row">
                        <div class="col-md-12 mb-7">
                            <label for="name" class=" required form-label">{{__('Name')}}</label>
                            <input type="text" id="name"
                                   class="form-control form-control-solid @error('name') is-invalid @enderror"
                                   name="name" value="{{ old('name') }}" required autofocus>
                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-7">
                            <label for="email" class="required form-label">{{__('Email')}}</label>
                            <input type="email" id="email"
                                   class="form-control form-control-solid @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email') }}" required>
                            @error('email')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-7">
                            <label for="mobile" class="required form-label">{{__('Mobile')}}</label>
                            <input type="number" id="mobile"
                                   class="form-control form-control-solid @error('mobile') is-invalid @enderror"
                                   name="mobile" value="{{ old('mobile') }}" required>
                            @error('mobile')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-7">
                            <label for="role_id" class="required form-label">{{__('Role Name')}}</label>
                            <select class="form-select form-select-solid fw-bolder" data-control="select2" required
                                    name="role_id" id="role_id" data-placeholder="{{__('Please Choose One')}}">
                                <option></option>
                                @foreach($roles as $role)
                                    <option
                                        @selected(old('role_id') === $role->id) value="{{$role->id}}">{{$role->name}}</option>
                                @endforeach
                            </select>
                            @error('role_id')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>


                        <div class="col-md-12 mb-7">
                            <label for="password" class="required form-label">{{__('Password')}}</label>
                            <input type="password" id="password"
                                   class="form-control form-control-solid @error('password') is-invalid @enderror"
                                   name="password"
                                   required>
                            @error('password')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>

                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light"
                        data-bs-dismiss="modal">{{__('Discard')}}</button>
                <button type="submit" class="btn btn-primary">{{__('Submit')}}</button>
            </div>
        </form>

    </div>
</div>
