<table id="example1" class="table table-bordered table-striped table-hover tbody-count">
      <thead>
      <tr>
        <th> S.No</th>
        <th> Date</th>
        <th> Type</th>
        <th> Action</th>
      </tr>
        </thead>
        <tbody id="schedule_tbody">
        <?php  if($DataList) {
            foreach($DataList as $key=>$value): ?>
                <tr id="row_<?php echo $value['_id']->{'$id'}; ?>">
              <td><?php echo $key+1; ?>   </td>
                  <td><?php echo date('Y-m-d',$value['date']); ?> </td>
                  <td><?php echo ($search_type>0)?$search_type_list[$search_type]:implode(',',$value['type']); ?> </td>
                  <td>
                  <a class="btn btn-primary btn-xs set_value" title="View" href="<?php echo base_url('admin/provider/schedule-list/').$value['userid']->{'$id'}.'';?>" >
                  <i class="fa fa-eye"></i></a> 
              </td>
                </tr>
        <?php endforeach; } ?>
        </tbody>
        <tfoot>
        </tfoot>
</table>