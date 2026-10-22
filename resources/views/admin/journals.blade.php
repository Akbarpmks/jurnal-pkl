@extends('layouts.admin')

@section('content')

{{-- =========================
     HEADER
========================= --}}

<div class="mb-4">

    <h2 class="page-title">
        📘 Semua Jurnal
    </h2>

    <p class="page-subtitle">
        Menampilkan seluruh jurnal dari semua pengguna.
    </p>

</div>


{{-- =========================
     SEARCH
========================= --}}

<form
    action="{{ route('admin.journals') }}"
    method="GET"
    class="row g-2 mb-4"
>

    {{-- SEARCH --}}

    <div class="col-md-6">

        <input
            type="text"
            name="search"
            class="form-control search-box"
            placeholder="Cari nama user, hari, pekerjaan, atau catatan..."
            value="{{ request('search') }}"
        >

    </div>


    {{-- BUTTON CARI --}}

    <div class="col-md-2">

        <button
            type="submit"
            class="btn btn-primary w-100"
        >

            <i class="bi bi-search"></i>

            Cari

        </button>

    </div>


    {{-- PER PAGE --}}

    <div class="col-md-2">

        <select
            name="per_page"
            class="form-select"
            onchange="this.form.submit()"
        >

            <option
                value="10"
                {{ request('per_page') == 10 ? 'selected' : '' }}
            >
                10 / halaman
            </option>

            <option
                value="25"
                {{ request('per_page') == 25 ? 'selected' : '' }}
            >
                25 / halaman
            </option>

            <option
                value="50"
                {{ request('per_page', 50) == 50 ? 'selected' : '' }}
            >
                50 / halaman
            </option>

            <option
                value="100"
                {{ request('per_page') == 100 ? 'selected' : '' }}
            >
                100 / halaman
            </option>

        </select>

    </div>


    {{-- RESET --}}

    @if(request('search'))

        <div class="col-md-2">

            <a
                href="{{ route('admin.journals') }}"
                class="btn btn-outline-secondary w-100"
            >
                Reset
            </a>

        </div>

    @endif

</form>


{{-- =========================
     CARD TABLE
========================= --}}

<div
    class="card border-0 shadow-sm"
    style="border-radius:16px;"
>

    <div class="card-body p-4">

        <div class="table-responsive">

            <table
                class="table table-bordered table-hover align-middle mb-0"
            >

                {{-- =========================
                     TABLE HEADER
                ========================= --}}

                <thead
                    style="
                        background:#2563eb;
                        color:white;
                    "
                >

                    <tr>

                        <th style="width:60px;">
                            No
                        </th>

                        <th>
                            Pengguna
                        </th>

                        <th>
                            Tanggal
                        </th>

                        <th>
                            Hari
                        </th>

                        <th>
                            Unit Kerja
                        </th>

                        <th>
                            Catatan
                        </th>

                        <th
                            class="text-center"
                            style="width:130px;"
                        >
                            Dokumentasi
                        </th>

                    </tr>

                </thead>


                {{-- =========================
                     TABLE BODY
                ========================= --}}

                <tbody>

                    @forelse($journals as $journal)

                        <tr>

                            {{-- =========================
                                 NO
                            ========================= --}}

                            <td>

                                {{ $journals->firstItem() + $loop->index }}

                            </td>


                            {{-- =========================
                                 PENGGUNA
                            ========================= --}}

                            <td>

                                <div class="d-flex align-items-center">

                                    <div
                                        class="
                                            rounded-circle
                                            bg-primary
                                            text-white
                                            d-flex
                                            justify-content-center
                                            align-items-center
                                            me-2
                                        "
                                        style="
                                            width:35px;
                                            height:35px;
                                            min-width:35px;
                                        "
                                    >

                                        {{
                                            strtoupper(
                                                mb_substr(
                                                    $journal->user->name ?? '?',
                                                    0,
                                                    1
                                                )
                                            )
                                        }}

                                    </div>


                                    <strong>

                                        {{
                                            $journal->user->name
                                            ?? 'User tidak ditemukan'
                                        }}

                                    </strong>

                                </div>

                            </td>


                            {{-- =========================
                                 TANGGAL
                            ========================= --}}

                            <td>

                                {{
                                    \Carbon\Carbon::parse(
                                        $journal->tanggal
                                    )->translatedFormat('d M Y')
                                }}

                            </td>


                            {{-- =========================
                                 HARI
                            ========================= --}}

                            <td>

                                {{ $journal->hari }}

                            </td>


                            {{-- =========================
                                 UNIT KERJA
                            ========================= --}}

                            <td>

                                {!! nl2br(e($journal->unit_kerja)) !!}

                            </td>


                            {{-- =========================
                                 CATATAN
                            ========================= --}}

                            <td>

                                {{ $journal->catatan ?: '-' }}

                            </td>


                            {{-- =========================
                                 DOKUMENTASI
                            ========================= --}}

                            <td class="text-center">

                                @if($journal->dokumentasi)

                                    <button
                                        type="button"
                                        class="btn p-0 border-0"
                                        data-bs-toggle="modal"
                                        data-bs-target="#dokumentasiModal"
                                        data-image="{{ asset('storage/' . $journal->dokumentasi) }}"
                                    >

                                        <img
                                            src="{{ asset('storage/' . $journal->dokumentasi) }}"
                                            alt="Dokumentasi kegiatan"
                                            class="rounded border"
                                            style="
                                                width:80px;
                                                height:60px;
                                                object-fit:cover;
                                                cursor:pointer;
                                            "
                                        >

                                    </button>

                                @else

                                    <span class="text-muted small">

                                        <i class="bi bi-image"></i>

                                        Tidak ada

                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="text-center text-muted py-5"
                            >

                                <i class="bi bi-journal-x fs-1 d-block mb-2"></i>

                                Tidak ada jurnal yang ditemukan.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =========================
             PAGINATION
        ========================= --}}

        @if($journals->hasPages())

            <div class="d-flex justify-content-center mt-4">

                {{ $journals->links('pagination::bootstrap-5') }}

            </div>

        @endif

        </div>

    </div>

</div>



{{-- =====================================================
     MODAL DOKUMENTASI
===================================================== --}}

<div
    class="modal fade"
    id="dokumentasiModal"
    tabindex="-1"
    aria-labelledby="dokumentasiModalLabel"
    aria-hidden="true"
>

    <div
        class="modal-dialog modal-dialog-centered modal-xl"
    >

        <div
            class="modal-content border-0 shadow"
            style="
                border-radius:18px;
                overflow:hidden;
            "
        >

            {{-- =========================
                 HEADER MODAL
            ========================= --}}

            <div class="modal-header">

                <h5
                    class="modal-title fw-bold"
                    id="dokumentasiModalLabel"
                >

                    <i class="bi bi-image me-2"></i>

                    Dokumentasi Kegiatan

                </h5>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>


            {{-- =========================
                 BODY MODAL
            ========================= --}}

            <div
                class="modal-body text-center"
                style="
                    background:#f8f9fa;
                    padding:20px;
                "
            >

                <img
                    id="dokumentasiPreview"
                    src=""
                    alt="Dokumentasi kegiatan"
                    class="img-fluid rounded shadow-sm"
                    style="
                        max-height:75vh;
                        max-width:100%;
                        width:auto;
                        object-fit:contain;
                    "
                >

            </div>

        </div>

    </div>

</div>



{{-- =====================================================
     SCRIPT MODAL
===================================================== --}}

<script>

document.addEventListener('DOMContentLoaded', function () {

    const dokumentasiModal =
        document.getElementById('dokumentasiModal');

    const dokumentasiPreview =
        document.getElementById('dokumentasiPreview');


    if (!dokumentasiModal || !dokumentasiPreview) {
        return;
    }


    // Saat modal dibuka

    dokumentasiModal.addEventListener(
        'show.bs.modal',
        function (event) {

            const button = event.relatedTarget;

            if (!button) {
                return;
            }

            const imageUrl =
                button.getAttribute('data-image');

            dokumentasiPreview.src = imageUrl;

        }
    );


    // Saat modal ditutup

    dokumentasiModal.addEventListener(
        'hidden.bs.modal',
        function () {

            dokumentasiPreview.src = '';

        }
    );

});

</script>

@endsection