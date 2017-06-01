@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <table class="table table-bordered" id="news-table">
            <thead>
            <tr>
                <th>Id</th>
                <th>Url</th>
                <th>Upvotes</th>
                <th>Downvotes</th>
                <th>Verified</th>
                <th>Options</th>
            </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        $('#news-table').DataTable({
            processing: true,
            serverSide: true,
            lengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]],
            pageLength: 20,
            processing: false,
            order: [[ 0, "desc" ]],
            ajax: '{!! route('datatables.news') !!}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'url', name: 'url',
                    render: function (data, type, row) {
                        return '<a href="' + row.url + '">'+ row.url + '</a>';
                    }
                },
                { data: 'upvotes', name: 'upvotes', className: "dt-center"},
                { data: 'downvotes', name: 'downvotes', className: "dt-center"},
                { data: 'verified', name: 'verified', className: "dt-center",
                    render: function (verified) {
                        return verified == '1' ? '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>' : '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>';
                    }
                },
                { data: 'options', orderable: false, className: "dt-center",
                    render: function (data, type, row) {
                        return '<a href="{!! Request::root() !!}/admin/verify/' + row.id + '">' + '<a<span class="glyphicon glyphicon-wrench" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Click to verify / Unverify"></span>' + '</a>';
                    }
                },
            ]
        });
    });
</script>
@endpush
