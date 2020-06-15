<p>Please wait...</p>

<script>
const params = window.location.search;

if (window.opener) {
    window.opener.postMessage({
        payload: params,
        source: window.name
    });
    window.close();
}
</script>
