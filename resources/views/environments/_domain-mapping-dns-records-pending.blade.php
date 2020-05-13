<p class="mb-2">Add the following DNS records to your registrar to point the domain to Cloud Run:<p>

<table class="mb-2 w-full border border-gray-300">
    <thead>
        <tr>
            <th class="text-left font-medium p-1 bg-gray-100">Type</th>
            <th class="text-left font-medium p-1 bg-gray-100">Name</th>
            <th class="text-left font-medium p-1 bg-gray-100">Content</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($records as $record)
        <tr class="border-t border-gray-300">
            <td class="p-1 font-mono">{{ $record['type'] }}</td>
            <td class="p-1 font-mono">{{ $record['name'] }}</td>
            <td class="p-1 font-mono">{{ $record['rrdata'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<p>After you add the DNS records, Cloud Run will issue a TLS certificate for your domain. This may take up to 15 minutes.</p>
