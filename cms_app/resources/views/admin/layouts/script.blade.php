<script type="text/javascript" src="{{ asset('js/app.js') }}"></script>
@yield('script')
<script type="text/javascript">
    document.getElementById("app").classList.remove("hide-body");
    document.getElementById("app").classList.add("show-body");
    document.getElementById("loader").classList.remove("show-loader");
    document.getElementById("loader").classList.add("hide-loader");
</script>