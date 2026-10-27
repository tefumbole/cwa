@if(in_array('letter_delete', $all_permission ?? []))
    <li>
        <a href="{{ route('letter.delete', $item->id) }}" class="btn btn-link" onclick="return confirm('Delete this letter?');"><i class="dripicons-trash"></i> Delete</a>
    </li>
@endif
