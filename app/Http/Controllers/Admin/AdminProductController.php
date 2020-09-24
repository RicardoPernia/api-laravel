<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Detail_Income;
use App\Models\Income;
use App\Models\Inventary;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $rules=[
            'product_name'=>'required|string',
            'product_description'=>'required|string',
            'product_price'=>'required|regex:/^\d+(\.\d{1,2})?$/',
            'category_id'=>'required|integer',
            'stock_max'=>'required|min:40|integer',
            'stock_min'=>'required|min:10|integer',
            'cant_product_current'=>'required|min:6|integer'

        ];
    public function __construct()
    {
        $this->name ='user';
        $this->model = new Admin();
        $this->namePlural = 'users';


    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,$id)
    {
        $admin=$this->_getInstance($id);

        $validations=$this->validateColumns($request,$this->rules);
        $getErrorsValidations=$this->_validateError($validations);
        if($getErrorsValidations)return $getErrorsValidations;

        $products=$this->_saveProducts($request,$admin->user_id);

        return $this->responseSuccesfully($products);
    }
    public function validateColumns($request,$rules){
        $data=[];
        for($i=0;$i<count($request->all());$i++){
            $requestArray=(array) $request[$i];
            array_push($data,$this->_validateRequest($requestArray,$rules));
        }
        return $data;

    }
   private function getDataInventary($request){
        $data=[];
        $data['cant_product_current']=$request['cant_product_current'];
        $data['stock_max']=$request['stock_max'];
        $data['stock_min']=$request['stock_min'];
        return $data;
    }
    private function getDataProduct($request){
        $data=[];
        $data['product_name']=$request['product_name'];
        $data['product_description']=$request['product_description'];
        $data['product_price']=$request['product_price'];
        $data['category_id']=$request['category_id'];
        return $data;
    }
    private function _saveProducts($request,$id){
       $data=[];
        $detail_income=[];
        $income=Income::create(['user_id'=>$id]);
        for($i=0;$i<count($request->all());$i++){
            $dataProduct=$this->getDataProduct($request[$i]);

            $dataInventary=$this->getDataInventary($request[$i]);
            $newProduct=Product::create($dataProduct);
            $dataInventary['product_id']=$newProduct->product_id;

            $newInventary=Inventary::create($dataInventary);
            $dataDetail=[
                'product_id'=>$newProduct->product_id,
                'quantity'=>$newInventary->cant_product_current,
                'price'=>$newProduct->product_price,
                'income_id'=>$income->income_id
            ];


            $detailIncome=Detail_Income::create($dataDetail);
            array_push($detail_income,$dataDetail);


        }
            $data['income']=$income;
            $data['detail_incomes']=$detail_income;
        return $data;

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function show( $id)
    {
        $admin=$this->_getInstance($id);

        $inventaries=$this->_GetRelations($admin->detail_incomes,'inventary');
        $products=$this->_GetRelations($inventaries,'product');
        return $this->responseSuccesfully($products);

    }




    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Admin $admin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function destroy(Admin $admin)
    {
        //
    }
}
