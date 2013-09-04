
<form enctype="multipart/form-data" id=news-form method="post" action="/index.php">
    <input  type="hidden" name="act" value="category-add">
    <table>
    <tr>
        <td>Header</td>
        <td>
            <input name="header" type="text">
        </td>
    </tr>
    <tr>
        <td colspan="2" ><input value="Exit" onclick="d.close()" type="button"><input value="Add category" type="submit"></td>
    </tr>
    </table>
</form>