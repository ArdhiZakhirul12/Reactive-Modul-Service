@extends('providermanagement::layouts.master')

@section('title', translate('Request_Details'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <h2 class="page-title">{{ translate('request_Details') }}</h2>
                    @if ($post->status == 'Pending')
                        <button class="btn btn--danger ms-auto" data-bs-toggle="modal" data-bs-target="#rejectCustomModal"
                            data-toggle="tooltip">
                            <span class="material-symbols-outlined">cancel</span>{{ translate('Batalkan') }}
                        </button>
                    @endif
                </div>
                <div class="modal fade" id="rejectCustomModal" tabindex="-1" aria-labelledby="rejectCustomModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('provider.booking.custom-request.reject', ['id' => $post->id]) }}"
                                method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title" id="rejectCustomModalLabel">
                                        {{ translate('Batalkan Permintaan') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="note" class="form-label">{{ translate('Catatan') }}</label>
                                        <textarea class="form-control" id="note" name="note" rows="3" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">{{ translate('Tutup') }}</button>
                                    <button type="submit" class="btn btn-danger">{{ translate('kirim') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                {{-- @dd($post->user) --}}
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="card bg-primary-light shadow-none">
                                    <div class="card-body pb-5">
                                        <div class="media flex-wrap gap-3">
                                            <img width="140" class="radius-10"
                                                src="{{ onErrorImage(
                                                    $post?->user?->profile_image,
                                                    asset('storage/app/public/user/profile_image') . '/' . $post?->user?->profile_image,
                                                    asset('public/assets/placeholder.png'),
                                                    'user/profile_image/',
                                                ) }}"
                                                alt="{{ translate('profile_image') }}">
                                            <div class="media-body">
                                                <div class="d-flex align-items-center gap-2 mb-3">
                                                    <span class="material-icons text-primary">person</span>
                                                    <h4>{{ translate('Customer Information') }}</h4>
                                                </div>
                                                <h5 class="text-primary mb-2">
                                                    {{ $post?->user?->first_name . ' ' . $post?->user?->last_name }}</h5>

                                                {{-- <div class="fs-12 text-muted">0.8km away from you</div> --}}

                                                {{-- <p class="text-muted fs-12">
                                                    @if ($distance)
                                                        {{$distance}} {{translate('away from you')}}
                                                    @endif
                                                </p> --}}
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <span class="material-icons">phone_iphone</span>
                                                    <a
                                                        href="tel:{{ $post?->user?->phone }}">{{ $post?->user?->phone }}</a>
                                                </div>
                                                {{-- @dd($post->address) --}}
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="material-icons">map</span>
                                                    <p>{{ Str::limit($post?->address?->address ?? translate('not_available'), 100) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card bg-primary-light shadow-none">
                                    <div class="card-body pb-5">
                                        <div class="d-flex align-items-center gap-2 mb-3">
                                            <img width="18"
                                                src="{{ asset('public/assets/provider-module') }}/img/media/more-info.png"
                                                alt="">
                                            <h4>{{ translate('Service Information') }}</h4>
                                        </div>
                                        <div class="media gap-2 mb-4">
                                            {{-- <img width="30"
                                                 src="{{onErrorImage(
                                                            $post?->sub_category?->image,
                                                            asset('storage/app/public/category').'/' . $post?->sub_category?->image,
                                                            asset('public/assets/placeholder.png') ,
                                                            'category/')}}"
                                                 alt="{{ translate('sub_category') }}"> --}}
                                            <div class="media-body">
                                                <h5>{{ $post?->service?->name }}</h5>
                                                <div class="text-muted fs-12">{{ $post?->sub_category?->name }}</div>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column gap-2">
                                            <div class="fw-medium">{{ translate('Machine Type') }} : <span
                                                    class="fw-bold">{{ $post->machine_name }}</span>
                                            </div>
                                            <div class="fw-medium">{{ translate('No Seri Mesin') }} : <span
                                                    class="fw-bold">{{ $post->no_seri }}</span>
                                            </div>
                                            <div class="fw-medium">{{ translate('Booking Request Time') }} : <span
                                                    class="fw-bold">{{ $post->created_at->format('d/m/Y h:ia') }}</span>
                                            </div>
                                            <div class="fw-medium">{{ translate('Description') }} : <span
                                                    class="fw-bold">{{ $post->description }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if ($post->status == 'Reject')
                                <div class="col-lg-6">
                                    <div class="card bg-primary-light shadow-none">
                                        <div class="card-body pb-5">
                                            <div class="d-flex align-items-center gap-2 mb-3">
                                                <img width="18"
                                                    src="{{ asset('public/assets/provider-module') }}/img/media/more-info.png"
                                                    alt="">
                                                <h4>{{ translate('Catatan Reject') }}</h4>
                                            </div>

                                            <div class="d-flex flex-column gap-2">
                                                <div class="fw-medium">{{ $post->cancellation_note }}
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Form create booking --}}
                            {{-- @dd($post->status) --}}
                            @if ($post->status == 'Pending')
                                <form
                                    action="{{ route('provider.booking.custom-request.store', [$post->id, 'zone_id' => "$provider_zone->zone_id"]) }}"
                                    method="post" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <h4 class="c1 mb-20">{{ translate('Create Booking') }}</h4>
                                        {{-- <div class="col-md-6">
                                        <label class="mb-2" for=""> {{translate('Head_Of_Technician')}} </label>
                                        <select class="select-identity theme-input-style mb-30" name="provider_id" required>
                                            <option>{{translate('Select_Head_Of_Technician')}}</option>
                                            @foreach ($providers as $provider)
                                                <option value="{{$provider->id}}">{{$provider->company_name}}</option>
                                            @endforeach
                                        </select>
                                    </div> --}}
                                    <input type="hidden" name="no_seri" value="{{$post->no_seri}}">
                                        <div class="col-md-6">
                                            <label class="mb-2" for=""> {{ translate('Machine_Type') }} </label>
                                            <select class="select-identity theme-input-style mb-30" id="machine_select"
                                                name="sub_category_id" required>
                                                <option value="{{ $machines->id }}">{{ $machines->name }}</option>
                                                {{-- <option>{{translate('Select_Machine')}}</option> --}}

                                            </select>
                                        </div>
                                        {{-- <div class="col-md-6">
                                        <label class="mb-2" for=""> {{translate('Service')}} </label>
                                        <select class="select-identity theme-input-style mb-30" id="service_select" name="service_id" required>
                                            <option value="">{{translate('Select_Service')}}</option>
                                            @foreach ($machines->services as $service)                                            
                                            <option value="{{ $service->id }}">{{$service->name}}</option>                                                                                            
                                            @endforeach
                                        </select>
                                    </div> --}}
                                        <div class="col-md-6 d-flex align-items-center">
                                            <div class="me-2 w-100">
                                                <label class="mb-2" for="">{{ translate('Service') }}</label>
                                                <select class="select-identity theme-input-style form-control mb-30"
                                                    id="service_select" name="service_id" required>
                                                    <option value="">{{ translate('Select_Service') }}</option>
                                                    @foreach ($machines->services as $service)
                                                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button type="button"
                                                onclick="window.location.href='{{ route('provider.service.create') }}'"
                                                class=" btn btn--primary d-flex align-items-center">
                                                <span class="material-icons"
                                                    title="{{ translate('add_new_employee') }}">add</span>
                                            </button>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="mb-2" for=""> {{ translate('Service_Schedule') }}
                                            </label>

                                            <input type="datetime-local" class="form-control h-45 mb-30"
                                                name="service_schedule" value="" id="service_schedule"
                                                onchange="service_schedule_update()">
                                        </div>

                                        {{-- <h4 class="c1 mb-20">{{translate('Create Variatons')}}</h4>
                                    <section>
                                        <div class="d-flex flex-wrap gap-20 mb-3">
                                            <div class="form-floating flex-grow-1">
                                                <select class="select-identity theme-input-style mb-30" id="variation_select" name="variation_key">
                                                    <option value="">{{translate('Select_Variant')}}</option>
                                                </select>
                                            </div>
                                            <div class="form-floating flex-grow-1">
                                                <input type="number" class="form-control" name="quantity"
                                                       id="variant-price"
                                                       placeholder="{{translate('Quantity')}} *" required="" value="0">
                                                <label>{{translate('Quantity')}} *</label>
                                            </div>
                                            <input type="hidden" name="quantitys" id="quantitys">
                                            <input type="hidden" name="variant_keys" id="variants">
                                            <button type="button" class="btn btn--primary mb-30" id="service-variation">
                                                <span class="material-icons">add</span>
                                                {{translate('add')}}
                                            </button>
                                        </div>

                                        <div class="table-responsive p-01">
                                            <table class="table align-middle table-variation">
                                                <thead id="category-wise-zone" class="text-nowrap">
                                                <tr>
                                                    <th scope="col">{{translate('variant')}}</th>
                                                    <th scope="col">{{translate('quantity')}}</th>
                                                    <th scope="col">{{translate('action')}}</th>
                                                </tr>
                                                </thead>
                                                <tbody id="variation-update-table">

                                                </tbody>
                                            </table>


                                        </div>
                                    </section> --}}
                                        <div class="d-flex gap-4 flex-wrap justify-content-end">
                                            <button type="reset"
                                                class="btn btn--secondary">{{ translate('Reset') }}</button>
                                            <button type="submit"
                                                class="btn btn--primary">{{ translate('Submit') }}</button>
                                        </div>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const addButton = document.getElementById("service-variation");
            const variationSelect = document.getElementById("variation_select");
            const quantityInput = document.getElementById("variant-price");
            const tableBody = document.getElementById("variation-update-table");
            const variantsInput = document.getElementById("variants");
            const quantitysInput = document.getElementById("quantitys");

            addButton.addEventListener("click", function() {
                const variant = variationSelect.value;
                const quantity = parseInt(quantityInput.value); // Parse quantity as integer

                if (variant !== "" && !isNaN(quantity) && quantity >= 0) {
                    const newRow = document.createElement("tr");
                    newRow.innerHTML = `
                        <td>${variant}</td>
                        <td>${quantity}</td>
                        <td><button class="btn btn-danger btn-sm delete-row">Delete</button></td>
                    `;
                    tableBody.appendChild(newRow);

                    // Update hidden inputs with new data
                    updateHiddenInputs();

                    // Reset input fields after adding row
                    variationSelect.value = "";
                    quantityInput.value = "0";
                } else {
                    alert("Please enter a valid quantity.");
                }
            });

            // Delete row when delete button is clicked
            tableBody.addEventListener("click", function(event) {
                if (event.target.classList.contains("delete-row")) {
                    event.target.closest("tr").remove();
                    // Update hidden inputs after deletion
                    updateHiddenInputs();
                }
            });

            // Function to update hidden inputs with current data
            function updateHiddenInputs() {
                const variants = [];
                const quantitys = [];

                // Iterate through table rows to extract data
                const rows = tableBody.querySelectorAll("tr");
                rows.forEach(row => {
                    const cells = row.querySelectorAll("td");
                    variants.push(cells[0].innerText);
                    quantitys.push(parseInt(cells[1].innerText)); // Parse quantity as integer
                });

                // Update hidden inputs with arrays
                variantsInput.value = JSON.stringify(variants);
                quantitysInput.value = JSON.stringify(quantitys);

            }
        });
    </script>

    <script>
        document.getElementById('machine_select').addEventListener('change', async function() {
            var machineId = this.value;
            var serviceSelect = document.getElementById('service_select');
            serviceSelect.innerHTML = '<option value="">{{ translate('Select_Service') }}</option>';
            // Jika tidak ada pilihan "Machine" yang dipilih, berhenti di sini
            const url = "{{ env('APP_URL') }}";
            // console.log(url);

            if (!machineId) return;
            // Kirim permintaan AJAX untuk mendapatkan daftar layanan berdasarkan sub_category_id
            // const baseUrl = `${window.location.origin}/Impach/Macfast-X-MST/admin/booking/custom-request`
            const url1 = await `${url}/provider/booking/custom-request`
            // console.log(url1)
            const response = await fetch(`${url1}/get-services/${machineId}`);
            // console.log(response.json())
            let data = await response.json();
            data.forEach((item) => {
                var option = document.createElement('option');
                option.value = item.id;
                option.text = item.name;
                serviceSelect.appendChild(option);
            })

        });

        document.getElementById('service_select').addEventListener('change', async function() {
            var serviceId = this.value;
            var variationSelect = document.getElementById('variation_select');
            variationSelect.innerHTML = '<option value="">{{ translate('Select_Variant') }}</option>';
            const url = "{{ env('APP_URL') }}";
            // console.log();
            // Jika tidak ada pilihan "Machine" yang dipilih, berhenti di sini
            if (!serviceId) return;
            // Kirim permintaan AJAX untuk mendapatkan daftar layanan berdasarkan sub_category_id
            // const baseUrl = `${window.location.origin}/MSTServiceCenter/admin/booking/custom-request`
            const baseUrl = await `${url}/provider/booking/custom-request`
            const response = await fetch(`${baseUrl}/get-variations/${serviceId}`);
            // console.log(response);
            let data = await response.json();
            data.forEach((item) => {
                var option = document.createElement('option');
                option.value = item.variant_key;
                option.text = item.variant;
                variationSelect.appendChild(option);
            })

        });
    </script>
@endsection
