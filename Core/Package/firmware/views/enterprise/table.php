<table class="warframe_table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Contact</th>
            <th style="width: 357px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($table as $row): ?>
            <tr <?php if($row->is_delete) echo 'style="background-color:red; color:white;"' ?>>
                <td>
                    <?php
                    if ($row->url) echo "<a href=\"{$row->url}\">{$row->name}</a>";
                    else echo $row->name;
                    ?>
                </td>
                <td><?= $row->contact ?></td>
                <td>
                    <?php if($row->url): ?>
                        <button onclick="AjaxQuery('/firmwareEnterprise/sync/<?= $row->id ?>')" type="button" class="warframe_btn" title="Synchronization">
                            Sync
                        </button>
                    <?php endif; ?>
                    <button onclick="checkModal('/firmwareEnterprise/getWebhook/<?= $row->id ?>')" type="button" class="warframe_btn" title="Api Key">
                        Api
                    </button>
                    <button onclick="checkModal('/firmwareEnterprise/get/<?= $row->id ?>')" type="button" class="warframe_btn" title="Edit">
                        Edit
                    </button>
                    <?php if($row->is_delete): ?>
                        <button onclick="AjaxQuery('/firmwareEnterprise/restore/<?= $row->id ?>')" type="button" class="warframe_btn" title="Restore">
                            Restore
                        </button>
                        <button onclick="AjaxQuery('/firmwareEnterprise/remove/<?= $row->id ?>')" type="button" class="warframe_btn" title="Delete">
                            Remove
                        </button>
                    <?php else: ?>
                        <button onclick="AjaxQuery('/firmwareEnterprise/delete/<?= $row->id ?>')" type="button" class="warframe_btn" title="Remove">
                            Delete
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $panel ?>