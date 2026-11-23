@extends('layouts.app')
@section('title', 'Edit Purchase '.$purchase->reference)
@section('content')
@include('purchases._form')
@endsection
