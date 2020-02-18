 <table class="table-head-box table-center table table-striped table-responsive checkbox checkbox-primary costing_product_table hello" data-toggle="datatables">

              <thead>
                <tr class="bg-primary">
                    <th><label><input class="form-check-input" type="checkbox" name="chkall_costing" id="chkall_costing"><span class="label-text"></span></label></th>
                    <th>Image</th>
                    <th>Sku</th>
                    <th>Vendor</th>
                    <th>Cno</th>
                    <th>Detail</th>
                </tr>
                </thead>
                <tbody>
                
                @foreach ($costingdatas as $key => $costingdata)
                <?php $Image = URL::to('/') .'/'.$costingdata->image; 
                      $costing_id = $costingdata->costingdata_id;
                      $costraw = \App\Costing::where('costing_id',$costing_id)->first();
                      $vendor_id = $costraw->vendor_id;
                        $vendorColl = $vendor->where('id',$vendor_id)->first();
                      $vendor_name = $vendorColl->name;
                      $seive_size[$key] = explode(',',$costingdata->seive_size);
                      $material_mm_size[$key] = explode(',',$costingdata->material_mm_size);
                      $material_pcs[$key] = explode(',',$costingdata->material_pcs);
                      $material_weight[$key] = explode(',',$costingdata->material_weight);
                      $maxVal = max(count($seive_size[$key]),count($material_mm_size[$key]),count($material_pcs[$key]),count($material_weight[$key]));
                    ?>
                  <tr>
                    <td class="sorting_1">
                      <label><input type="checkbox" class="form-check-input chkProduct" name="chk_costing" id="chk_costing" value="<?php echo $costingdata->id; ?>">
                      <span class="label-text"></span>
                      </label>
                    </td>
                    <td><img src="{{ $Image }}" class="img-fluid" height="120" width="120"/></td>
                    <td>{{ $costingdata->sku }}</td>
                    <td>{{ $vendor_name }}</td>
                    <td>{{ $costingdata->certificate_no }}</td>
                    <td>
                        <a href="#"><i title="Detail" onclick="showDetail('<?php echo $costingdata->id;?>')"  class="material-icons list-icon">info</i></a>
                        <a href="#"><i title="Accept" data-id ='<?php echo $costingdata->id;?>' class="material-icons list-icon qc_btn <?php if($costingdata->qc_status == 1) { ?> disabled <?php } ?> " data-status="accept" id="accept">check_circle</i></a>
                        <a href="#"><i title="Reject" data-id ='<?php echo $costingdata->id;?>' class="material-icons list-icon qc_btn <?php if($costingdata->qc_status == 0) { ?> disabled <?php } ?>" data-status="reject" id='reject'>cancel</i></a>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
                <tfoot>
                  <tr>
                    <th></th>
                    <th>Image</th>
                    <th>Sku</th>
                    <th>Vendor</th>
                    <th>Cno</th>
                    <th>Detail</th>
                  </tr>
                </tfoot>
        </table>
                  