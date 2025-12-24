<?php

namespace app\lib\LdlExcel;

use app\model\BasicModel;
use app\repository\BankDisbursementDownloadRepository;
use DI\Attribute\Inject;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Psr\Http\Message\ResponseInterface;

class PhpOffice extends LdlExcel implements ExcelPropertyInterface
{


    public function import(BasicModel $model, ?\Closure $closure = null): mixed
    {
        $request = request();
        $data = [];
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $tempFileName = 'import_' . time() . '.' . $file->getExtension();
            $tempFilePath = BASE_PATH . '/runtime/' . $tempFileName;
            file_put_contents($tempFilePath, $file->getStream()->getContents());
            $reader = IOFactory::createReader(IOFactory::identify($tempFilePath));
            $reader->setReadDataOnly(true);
            $sheet = $reader->load($tempFilePath);
            $endCell = isset($this->property) ? $this->getColumnIndex(count($this->property)) : null;
            try {
                if ($this->orderByIndex) {
                    $data = $this->getDataByIndex($sheet, $endCell);
                } else {
                    $data = $this->getDataByText($sheet, $endCell);
                }
                unlink($tempFilePath);
            } catch (\Throwable $e) {
                unlink($tempFilePath);
                throw new \Exception($e->getMessage());
            }
        } else {
            return false;
        }
        if ($closure instanceof \Closure) {
            return $closure($model, $data);
        }

        foreach ($data as $datum) {
            $model::create($datum);
        }
        return true;
    }

    public function export(string $filename, string $suffix, string $down_filepath, array|\Closure $closure, ?\Closure $callbackData = null, int $sheetIndex = 0): \support\Response
    {
        $spread = new Spreadsheet();
        // 确保Sheet存在
        if ($sheetIndex >= $spread->getSheetCount()) {
            // 如果需要的Sheet不存在，可以创建新Sheet
            $spread->createSheet($sheetIndex);
        }

        $sheet = $spread->setActiveSheetIndex($sheetIndex);
        $filename .= '.' . $suffix;

        is_array($closure) ? $data = &$closure : $data = $closure();

        // 表头
        $titleStart = 0;
        foreach ($this->property as $item) {
            $headerColumn = $this->getColumnIndex($titleStart) . '1';
            $sheet->setCellValue($headerColumn, $item['value']);
            $style = $sheet->getStyle($headerColumn)->getFont()->setBold(true);
            $columnDimension = $sheet->getColumnDimension($headerColumn[0]);

            empty($item['width']) ? $columnDimension->setAutoSize(true) :
                $columnDimension->setWidth((float)$item['width']);

            empty($item['align']) || $sheet->getStyle($headerColumn)->getAlignment()->setHorizontal($item['align']);

            empty($item['headColor']) || $style->setColor(new Color(str_replace('#', '', $item['headColor'])));

            if (!empty($item['headBgColor'])) {
                $sheet->getStyle($headerColumn)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB(str_replace('#', '', $item['headBgColor']));
            }
            ++$titleStart;
        }

        $generate = $this->yieldExcelData($data);

        // 表体
        try {
            $row = 2;
            while ($generate->valid()) {
                $column = 0;
                $items = $generate->current();
                if ($callbackData instanceof \Closure) {
                    $items = $callbackData($items);
                }
                foreach ($items as $name => $value) {
                    $columnRow = $this->getColumnIndex($column) . $row;
                    $annotation = '';
                    foreach ($this->property as $item) {
                        if ($item['name'] == $name) {
                            $annotation = $item;
                            break;
                        }
                    }

                    if (!empty($annotation['dictName'])) {
                        $sheet->setCellValue($columnRow, $annotation['dictName'][$value]);
                    } elseif (!empty($annotation['path'])) {
                        $sheet->setCellValue($columnRow, $items[$annotation['path']]);
                    } elseif (!empty($annotation['dictData'])) {
                        $sheet->setCellValue($columnRow, $annotation['dictData'][$value]);
                    } elseif (!empty($this->dictData[$name])) {
                        $sheet->setCellValue($columnRow, $this->dictData[$name][$value] ?? '');
                    } else {
                        $sheet->setCellValue($columnRow, $value . "\t");
                    }

                    if (!empty($item['color'])) {
                        $sheet->getStyle($columnRow)->getFont()
                            ->setColor(new Color(str_replace('#', '', $annotation['color'])));
                    }

                    if (!empty($item['bgColor'])) {
                        $sheet->getStyle($columnRow)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB(str_replace('#', '', $annotation['bgColor']));
                    }
                    ++$column;
                }
                $generate->next();
                ++$row;
            }
        } catch (\RuntimeException $e) {
        }

        $path = BASE_PATH . $down_filepath;
        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true) && !is_dir($path)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
            }
        }
        $address = $path . $filename;

        // $writerType 等于 $suffix 的首字母大写;
        $writerType = ucfirst($suffix);
        $writer = IOFactory::createWriter($spread, $writerType);
        $writer->save($address);
        $get_contents = file_get_contents($address);
        $res = $this->downloadExcel($filename, $address, $get_contents);
        $spread->disconnectWorksheets();
        return $res;
    }

    protected function yieldExcelData(array &$data): \Generator
    {
        foreach ($data as $dat) {
            $yield = [];
            foreach ($this->property as $item) {
                $yield[$item['name']] = $dat[$item['name']] ?? '';
            }
            yield $yield;
        }
    }

    private function getDataByIndex($sheet, $endCell): array
    {
        $data = [];
        foreach ($sheet->getActiveSheet()->getRowIterator(2) as $row) {
            $temp = [];
            foreach ($row->getCellIterator('A', $endCell) as $index => $item) {
                $propertyIndex = ord($index) - 65;
                if (isset($this->property[$propertyIndex])) {
                    $temp[$this->property[$propertyIndex]['name']] = $item->getFormattedValue();
                }
            }
            if (!empty($temp)) {
                $data[] = $temp;
            }
        }
        return $data;
    }

    private function getDataByText($sheet, $endCell): array
    {
        $data = [];
        // 获取展示名称到字段名的映射关系
        $fieldMap = [];
        foreach ($this->property as $item) {
            $fieldMap[trim($item['value'])] = $item['name'];
        }

        $headerMap = [];
        // 获取表头
        // 获取表头，假设表头在第一行
        $headerRow = $sheet->getActiveSheet()->getRowIterator(1, 1)->current();
        foreach ($headerRow->getCellIterator('A', $endCell) as $index => $item) {
            $propertyIndex = ord($index) - 65; // 获得列索引
            $value = trim($item->getFormattedValue());
            $headerMap[$propertyIndex] = $fieldMap[$value] ?? null; // 获取表头值
        }
        // 读取数据，从第二行开始
        foreach ($sheet->getActiveSheet()->getRowIterator(2) as $row) {
            $temp = [];
            foreach ($row->getCellIterator('A', $endCell) as $index => $item) {
                $propertyIndex = ord($index) - 65; // 获得列索引
                if (!empty($headerMap[$propertyIndex])) { // 确保列索引存在于表头数组中
                    $temp[$headerMap[$propertyIndex]] = trim($item->getFormattedValue()); // 映射表头标题到对应值
                }
            }
            $data[] = $temp;
        }
        return $data;
    }

}