# 媒體庫連接資料庫的版本 | MediaLibrary_Connect_DataBase
此為修改`ctf0/Laravel-Media-Manager`後的版本，主要多了連接資料庫，並包成模組化的功能。

## 來源 | Source
GitHub:https://github.com/ctf0/Laravel-Media-Manager

## 與來源的差別 | Difference From Source

- 瘦身

盡量減少手動，讓整體功能更加自動化一點，有去掉了來源套件自動產生出，但是我認為不是必需的前端檔案，像是：
```
public底下的assets資料夾 、
resources底下的assets資料夾、
resources/vendor/MediaManager底下的資料夾 、
讓可自動編譯/產生的前端CSS&JS檔案統一放在/public/modules/MediaLibrary資料夾底下。
```

- 連接資料表

資料表名稱

`media_library`

範例資料

id           | name  | type | path | 
--------------|:-----:|-----:| ----:|
17   | TESTPIC.png | image/png  |  /asd/123/456/TESTPIC.png   | 


- 媒體庫模組化

Laravel Module:https://nwidart.com/laravel-modules/v6/introduction

## 前置動作 | Setting
- 裝上Laravel Module

- 把此模組化的媒體庫的整個資料夾放到`projectRootDirectory/Modules/`資料夾底下

- 在使用到媒體庫的編輯欄位的頁面引入該欄位的HTML以及JS

```html
<div class="form-img-input form-row ml-2 mediaSelect" data-button-text="選擇"><input type="hidden" value="8" class="form-img-input form-row ml-2" name="img_id" id="field-img_id"></div>
<script src="{{ asset('modules/MediaLibrary/js/usage.js') }}"></script>
```
- 在該頁面引入Bootstrap Modal的HTML
```php
@include('medialibrary::components.bootstrapModal', ['titleText' => trans('tm.mediaLibrary'), 'modalName' => 'mediaLibraryContainer'])
```

Bootstrap Modal:https://getbootstrap.com/docs/4.0/components/modal/
- 刪除圖片

針對已經套用的該欄位(已經有存圖片的`id`值)，在刪除圖片時，要一併資料清除；內容寫在`MediaLibraryController.php`的`deleteItem()`裡。

- 編譯前端檔案

在`webpack.mix.js` 加入`javascript`以及`sass`編譯
```js
mix.js('Modules/MediaLibrary/Resources/assets/js/usage.js', 'public/modules/MediaLibrary/js')
    .sass('Modules/MediaLibrary/Resources/assets/sass/library.scss', 'public/modules/MediaLibrary/css')
```

## 使用中 | Using

- 取得媒體庫的圖片路徑

需在有使用到媒體庫欄位的相關function/Model裡面，像是`App/Post.php`這裡面的這function。
```php
public function getMedia($media_id = 0){
    $mediaLibrary = MediaLibraryModel::where('id', $media_id)->first();
    $src = ($mediaLibrary)
        ? asset('/storage/mediaLibrary/' . $mediaLibrary->path)
        : null;
    return $src;
}
```

- 如缺少檔案(發生`404 js error`)

如果有需要使用到`json` 像是載入動畫等等需要，直接到媒體庫`ctf0/Laravel-Media-Manager`來源的`GitHub`去找檔案，缺少的應是在`dist`資料夾底下的json，總之缺什麼就拿什麼來補上。(那我會沒有放進來的原因，是因為那些檔案基本上不是必須的。)

- 修改媒體庫內的前端檔案

之後可直接更改`/Modules/MediaLibrary/Resources/assets/js/usage.js`、`/Modules/MediaLibrary/Resources/assets/sass/library.scss`，
